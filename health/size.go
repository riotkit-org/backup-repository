package health

import (
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/collections"
	"github.com/riotkit-org/backup-repository/storage"
)

type VersionsSizeValidator struct {
	svc *storage.Service
}

func (v VersionsSizeValidator) Validate(c *collections.Collection) error {
	versions, err := v.svc.FindAllActiveVersionsFor(c.GetId())
	if err != nil {
		return errors.Wrapf(err, "Cannot list versions for collection id=%v", c.GetId())
	}

	maxVersionSize, err := c.GetMaxOneVersionSizeInBytes()
	if err != nil {
		return errors.Wrapf(err, "Cannot list versions for collection id=%v", c.GetId())
	}

	for _, v := range versions {
		if v.Filesize > maxVersionSize {
			return errors.Errorf("maximum filesize is bigger than collection soft limit per file. Failed file: %v (size=%vb)", v.Filename, v.Filesize)
		}
	}

	return nil
}

func NewVersionsSizeValidator(svc *storage.Service) VersionsSizeValidator {
	return VersionsSizeValidator{svc}
}
