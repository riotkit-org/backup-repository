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

func NewService(db *gorm.DB, driverUrl string, isUsingGCS bool) (Service, error) {
	repository := VersionsRepository{db: db}

	// Google Cloud requires extra support
	if isUsingGCS {
		gcsCredentials, err := gcp.DefaultCredentials(context.Background())
		if err != nil {
			return Service{}, errors.New(fmt.Sprintf("cannot grab credentials for Google Cloud Storage: %v", err))
		}
		client, loginErr := gcp.NewHTTPClient(gcp.DefaultTransport(), gcp.CredentialsTokenSource(gcsCredentials))
		if loginErr != nil {
			return Service{}, errors.New(fmt.Sprintf("cannot login to Google Cloud Storage: %v", loginErr))
		}
		driver, openErr := gcsblob.OpenBucket(context.Background(), client, driverUrl, nil)
		if openErr != nil {
			return Service{}, errors.New(fmt.Sprintf("cannot open Google Cloud Storage bucket: %v", openErr))
		}
		return Service{storage: driver, repository: &repository}, nil
	}

	driver, err := blob.OpenBucket(context.Background(), driverUrl)
	if err != nil {
		logrus.Errorf("Cannot construct storage driver: %v", err)
		return Service{}, err
	}

	return Service{storage: driver, repository: &repository}, nil
}
