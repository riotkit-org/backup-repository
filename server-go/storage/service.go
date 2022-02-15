package storage

import (
	"context"
	"github.com/sirupsen/logrus"
	"gocloud.dev/blob"
)

type Service struct {
	storage    *blob.Bucket
	repository *VersionsRepository
}

func (s *Service) FindNextVersionForCollectionId(name string) int {
	lastHigherVersion := s.repository.findLastHighestVersionNumber(name)
	return lastHigherVersion + 1
}

func NewService(driverUrl string) (Service, error) {
	driver, err := blob.OpenBucket(context.Background(), driverUrl)
	if err != nil {
		logrus.Errorf("Cannot construct storage driver: %v", err)
		return Service{}, err
	}

	return Service{storage: driver}, nil
}
