package storage

import (
	"context"
	"errors"
	"fmt"
	"github.com/google/uuid"
	"github.com/riotkit-org/backup-repository/collections"
	"github.com/sirupsen/logrus"
	"gocloud.dev/blob"
	_ "gocloud.dev/blob/fileblob"
	"gocloud.dev/blob/gcsblob"
	_ "gocloud.dev/blob/s3blob"
	"gocloud.dev/gcp"
	"gorm.io/gorm"
	"time"
)

type Service struct {
	storage    *blob.Bucket
	repository *VersionsRepository
}

func (s *Service) FindNextVersionForCollectionId(name string) (int, error) {
	lastHigherVersion, err := s.repository.findLastHighestVersionNumber(name)
	if err != nil {
		logrus.Errorf("[FindNextVersionForCollectionId] Attempted to find next version number for collectionId=%v. Got error: %v", name, err)
		return 0, err
	}
	return lastHigherVersion + 1, nil
}

func (s *Service) CreateNewVersionFromCollection(c *collections.Collection, uploader string, uploaderSessionId string, filesize int) (UploadedVersion, error) {
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

// NewService is a factory method that knows how to construct a Storage provider, distincting multiple types of providers
func NewService(db *gorm.DB, driverUrl string, isUsingGCS bool) (Service, error) {
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
		return Service{storage: driver, repository: &repository}, nil
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

	return Service{storage: driver, repository: &repository}, nil
}
