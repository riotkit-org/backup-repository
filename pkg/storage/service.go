package storage

import (
	"context"
	"errors"
	"fmt"
	"github.com/google/uuid"
	"github.com/riotkit-org/backup-repository/pkg/collections"
	"github.com/sirupsen/logrus"
	"gocloud.dev/blob"
	_ "gocloud.dev/blob/fileblob"
	"gocloud.dev/blob/gcsblob"
	_ "gocloud.dev/blob/s3blob"
	"gocloud.dev/gcp"
	"gorm.io/gorm"
	"io"
	"strings"
	"time"
)

type Service struct {
	storage       *blob.Bucket
	repository    *VersionsRepository
	HealthTimeout time.Duration
	IOTimeout     time.Duration
}

func (s *Service) FindNextVersionForCollectionId(name string) (int, error) {
	lastHigherVersion, err := s.repository.findLastHighestVersionNumber(name)
	if err != nil {
		logrus.Errorf("[FindNextVersionForCollectionId] Attempted to find next version number for collectionId=%v. Got error: %v", name, err)
		return 0, err
	}
	return lastHigherVersion + 1, nil
}

func (s *Service) CreateNewVersionFromCollection(c *collections.Collection, uploader string, uploaderSessionId string, filesize int64) (UploadedVersion, error) {
	nextVersion, err := s.FindNextVersionForCollectionId(c.Metadata.Name)
	if err != nil {
		return UploadedVersion{}, err
	}
	return UploadedVersion{
		Id:                  uuid.New().String(),
		CollectionId:        c.Metadata.Name,
		VersionNumber:       nextVersion,
		Filename:            c.GenerateNextVersionFilename(nextVersion),
		Filesize:            filesize,
		UploadedBySessionId: uploaderSessionId,
		Uploader:            uploader,
		CreatedAt:           time.Time{},
		UpdatedAt:           time.Time{},
		DeletedAt:           gorm.DeletedAt{},
	}, nil
}

func (s *Service) CreateRotationStrategyCase(collection *collections.Collection) (RotationStrategy, error) {
	foundVersions, err := s.repository.findAllVersionsForCollectionId(collection.Metadata.Name)
	if err != nil {
		return &FifoRotationStrategy{}, errors.New(fmt.Sprintf("cannot construct rotation strategy, cannot findAllVersionsForCollectionId, error: %v", err))
	}

	if collection.Spec.StrategyName == "fifo" {
		return NewFifoRotationStrategy(collection, foundVersions), nil
	}

	return &FifoRotationStrategy{}, errors.New(fmt.Sprintf("collection configuration error: unrecognized backup strategy type '%v'", collection.Spec.StrategyName))
}

func (s *Service) CleanUpOlderVersions(versions []UploadedVersion) bool {
	logrus.Infof("Cleaning up older versions")
	isError := false

	for _, version := range versions {
		if err := s.Delete(&version); len(err) > 0 {
			logrus.Errorf("Consistency error! Cannot delete version id=%v, of collectionId=%v, error: %v", version.Id, version.CollectionId, err)
			isError = true
		} else {
			logrus.Debugf("Deleted version: id=%v, version=%v, of collectionId=%v", version.Id, version.VersionNumber, version.CollectionId)
		}
	}

	return isError
}

func (s *Service) Delete(version *UploadedVersion) []error {
	var collectedErrors []error

	if err, _ := s.repository.delete(version); err != nil {
		if !strings.Contains(err.Error(), "code=NotFound") {
			collectedErrors = append(collectedErrors, errors.New(fmt.Sprintf("cannot delete version from database - id=%v, version=%v, error=%v", version.Id, version.VersionNumber, err)))
		}
	}

	if err := s.storage.Delete(context.TODO(), version.GetTargetPath()); err != nil {
		collectedErrors = append(collectedErrors, errors.New(fmt.Sprintf("cannot delete from storage at path '%v', error: %v", version.GetTargetPath(), err)))
	}

	return collectedErrors
}

func (s *Service) RegisterVersion(version *UploadedVersion) error {
	return s.repository.create(version)
}

func (s *Service) CalculateMaximumAllowedUploadFilesize(collection *collections.Collection, excluding []UploadedVersion) (int64, error) {
	maxExtraSpace, err := collection.GetEstimatedCollectionExtraSpace()
	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot calculate maximum allowed filesize for upload: %v", err))
	}
	maxOneVersionSize, err := collection.GetMaxOneVersionSizeInBytes()
	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot calculate maximum allowed filesize for upload: %v", err))
	}

	// collection does not have extra space
	if maxExtraSpace == 0 {
		logrus.Debugf("Collection id=%v does not have extra space", collection.GetId())
		return maxOneVersionSize, nil
	}

	currentVersionsInCollection, err := s.repository.findAllVersionsForCollectionId(collection.GetId())
	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot calculate maximum allowed filesize for upload: %v", err))
	}

	// collection has additional extra space to use
	usedExtraSpace, err := s.CalculateAllocatedSpaceAboveSingleVersionLimit(
		collection,
		currentVersionsInCollection,
		excluding,
	)
	logrus.Debugf("Remaining extra space in collection (id=%v) is %vb", collection.GetId(), usedExtraSpace)
	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot calculate maximum allowed filesize for upload: %v", err))
	}
	remainedExtraSpace := maxExtraSpace - usedExtraSpace

	if remainedExtraSpace < 0 {
		return 0, errors.New(fmt.Sprintf("weird thing happened, maxExtraSpace-usedExtraSpace gave minus result. Corrupted collection"))
	}

	return remainedExtraSpace + maxOneVersionSize, nil
}

func (s *Service) CalculateAllocatedSpaceAboveSingleVersionLimit(collection *collections.Collection, existing []UploadedVersion, excluding []UploadedVersion) (int64, error) {
	var ids []string
	var allocatedSpaceAboveLimit int64
	maxOneVersionSize, err := collection.GetMaxOneVersionSizeInBytes()
	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot calculate allocated space above single version limit in collection, error: %v", err))
	}

	for _, version := range excluding {
		ids = append(ids, version.Id)
	}

	for _, version := range existing {
		// optionally exclude selected versions
		if contains(ids, version.Id) {
			logrus.Debugf("Excluding version id=%v", version.Id)
			continue
		}
		if version.Filesize > maxOneVersionSize {
			diff := version.Filesize - maxOneVersionSize
			logrus.Debugf("Version id=%v is exceeding its limit by %v", version.Id, diff)
			allocatedSpaceAboveLimit += diff
		}
	}

	return allocatedSpaceAboveLimit, nil
}

func (s *Service) CreateStandardMiddleWares(context context.Context, versionsToDelete []UploadedVersion, collection *collections.Collection) (NestedStreamMiddlewares, error) {
	maxAllowedFilesize, err := s.CalculateMaximumAllowedUploadFilesize(collection, versionsToDelete)
	logrus.Debugf("CalculateMaximumAllowedUploadFilesize(%v) = %v", collection.GetId(), maxAllowedFilesize)

	if err != nil {
		return NestedStreamMiddlewares{}, errors.New(fmt.Sprintf("cannot construct standard middlewares, error: %v", err))
	}

	return NestedStreamMiddlewares{
		s.createRequestCancelledMiddleware(context),
		s.createQuotaMaxFileSizeMiddleware(maxAllowedFilesize),
		s.createNonEmptyMiddleware(),
		s.createGPGStreamMiddleware(),
	}, nil
}

func (s *Service) GetVersionByNum(collectionId string, version string) (UploadedVersion, error) {
	if version == "latest" {
		v, err := s.repository.findLastHighestVersionNumber(collectionId)
		if err != nil {
			return UploadedVersion{}, errors.New("cannot find version. probably the collection is empty")
		}
		version = fmt.Sprintf("%v", v)
	}

	return s.repository.getByVersionNum(collectionId, strings.TrimPrefix(version, "v"))
}

func (s *Service) FindLatestVersion(collectionId string) (UploadedVersion, error) {
	latestVersion, err := s.repository.findLastHighestVersionNumber(collectionId)
	if err != nil {
		return UploadedVersion{}, err
	}
	return s.repository.getByVersionNum(collectionId, fmt.Sprintf("%v", latestVersion))
}

func (s *Service) ReadFile(ctx context.Context, path string) (io.ReadCloser, error) {
	return s.storage.NewReader(ctx, path, &blob.ReaderOptions{})
}

func (s *Service) FindAllActiveVersionsFor(id string) ([]UploadedVersion, error) {
	return s.repository.findAllActiveVersions(id)
}

// TestReadWrite is performing a simple write & read & delete operation to check if storage is healthy
func (s *Service) TestReadWrite(parentCtx context.Context, timeout time.Duration) error {
	healthKey := fmt.Sprintf(".health-%v", time.Now().UnixNano())
	healthSecret := fmt.Sprintf("secret-%v", time.Now().Unix())

	ctx, cancel := context.WithTimeout(parentCtx, timeout)
	defer cancel()

	if err := s.storage.WriteAll(ctx, healthKey, []byte(healthSecret), &blob.WriterOptions{}); err != nil {
		return err
	}

	read, err := s.storage.ReadAll(ctx, healthKey)
	if err != nil {
		return err
	}
	if string(read) != healthSecret {
		return errors.New("cannot verify storage read&write - wrote a text, but read a different text")
	}

	if err := s.storage.Delete(ctx, healthKey); err != nil {
		return err
	}

	return nil
}

// NewService is a factory method that knows how to construct a Storage provider, distincting multiple types of providers
func NewService(db *gorm.DB, driverUrl string, isUsingGCS bool, healthTimeout time.Duration, ioTimeout time.Duration) (Service, error) {
	repository := VersionsRepository{db: db}

	// Google Cloud requires extra support
	if isUsingGCS {
		gcsCredentials, err := gcp.DefaultCredentials(context.TODO())
		if err != nil {
			return Service{}, errors.New(fmt.Sprintf("cannot grab credentials for Google Cloud Storage: %v", err))
		}
		client, loginErr := gcp.NewHTTPClient(gcp.DefaultTransport(), gcp.CredentialsTokenSource(gcsCredentials))
		if loginErr != nil {
			return Service{}, errors.New(fmt.Sprintf("cannot login to Google Cloud Storage: %v", loginErr))
		}
		driver, openErr := gcsblob.OpenBucket(context.TODO(), client, driverUrl, nil)
		if openErr != nil {
			return Service{}, errors.New(fmt.Sprintf("cannot open Google Cloud Storage bucket: %v", openErr))
		}
		if result, err := driver.IsAccessible(context.TODO()); err != nil || !result {
			logrus.Warningln("If connection status is still failing without a message then, check if bucket exists")
			return Service{}, errors.New(fmt.Sprintf("Google Cloud Storage bucket is not accessible: %v || connection status = %v", err, result))
		}
		return Service{storage: driver, repository: &repository, HealthTimeout: healthTimeout, IOTimeout: ioTimeout}, nil
	}

	// AWS S3, Min.io, CEPH and others compatible with S3 protocol
	driver, err := blob.OpenBucket(context.Background(), driverUrl)
	if err != nil {
		logrus.Errorf("Cannot construct storage driver: %v", err)
		return Service{}, err
	}
	if result, err := driver.IsAccessible(context.TODO()); err != nil || !result {
		logrus.Warningln("For S3-compatible adapters it may be need to set environment variables: AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY")
		logrus.Warningln("For Min.io example connection string is: 's3://mybucket?endpoint=localhost:9000&disableSSL=true&s3ForcePathStyle=true&region=eu-central-1'")
		logrus.Warningln("If connection status is still failing without a message then, check if bucket exists")
		return Service{}, errors.New(fmt.Sprintf("bucket is not accessible: %v || connection status = %v", err, result))
	}

	return Service{storage: driver, repository: &repository, HealthTimeout: healthTimeout, IOTimeout: ioTimeout}, nil
}

func contains(s []string, e string) bool {
	for _, a := range s {
		if a == e {
			return true
		}
	}
	return false
}
