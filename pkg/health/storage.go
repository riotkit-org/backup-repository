package health

import (
	"context"
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/pkg/storage"
	"time"
)

type StorageAvailabilityValidator struct {
	storage *storage.Service
	ctx     context.Context
	timeout time.Duration
}

func (v StorageAvailabilityValidator) Validate() error {
	err := v.storage.TestReadWrite(v.ctx, v.timeout)
	if err != nil {
		return errors.Wrapf(err, "storage not operable")
	}

	return nil
}

func NewStorageValidator(storage *storage.Service, ctx context.Context, timeout time.Duration) StorageAvailabilityValidator {
	return StorageAvailabilityValidator{storage, ctx, timeout}
}
