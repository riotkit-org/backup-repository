package health

import (
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/storage"
)

type StorageAvailabilityValidator struct {
	storage *storage.Service
}

func (v StorageAvailabilityValidator) Validate() error {
	err := v.storage.TestReadWrite()
	if err != nil {
		return errors.Wrapf(err, "storage not operable")
	}

	return nil
}

func NewStorageValidator(storage *storage.Service) StorageAvailabilityValidator {
	return StorageAvailabilityValidator{storage}
}
