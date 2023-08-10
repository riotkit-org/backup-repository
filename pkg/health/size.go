package health

import (
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/pkg/collections"
	"github.com/riotkit-org/backup-repository/pkg/storage"
)

type VersionsSizeValidator struct {
	svc *storage.Service
	c   *collections.Collection
}

func (v VersionsSizeValidator) Validate() error {
	versions, err := v.svc.FindAllActiveVersionsFor(v.c.GetId())
	if err != nil {
		return errors.Wrapf(err, "Cannot list versions for collection id=%v", v.c.GetId())
	}

	maxVersionSize, err := v.c.GetMaxOneVersionSizeInBytes()
	if err != nil {
		return errors.Wrapf(err, "Cannot list versions for collection id=%v", v.c.GetId())
	}

	for _, v := range versions {
		if v.Filesize > maxVersionSize {
			return errors.Errorf("maximum filesize is bigger than collection soft limit per file. Failed file: %v (size=%vb)", v.Filename, v.Filesize)
		}
	}

	return nil
}

func NewVersionsSizeValidator(svc *storage.Service, c *collections.Collection) VersionsSizeValidator {
	return VersionsSizeValidator{svc, c}
}
