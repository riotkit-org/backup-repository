package health

import (
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/collections"
	"github.com/riotkit-org/backup-repository/storage"
)

type SumOfVersionsValidator struct {
	svc *storage.Service
	c   *collections.Collection
}

func (v SumOfVersionsValidator) Validate() error {
	var totalSize int64
	allActive, _ := v.svc.FindAllActiveVersionsFor(v.c.GetId())

	for _, version := range allActive {
		totalSize += version.Filesize
	}

	maxCollectionSize, _ := v.c.GetCollectionMaxSize()

	if totalSize > maxCollectionSize {
		return errors.Errorf("Summary of all files is %vb, while collection hard limit is %vb", totalSize, maxCollectionSize)
	}

	return nil
}

func NewSumOfVersionsValidator(svc *storage.Service, c *collections.Collection) SumOfVersionsValidator {
	return SumOfVersionsValidator{svc, c}
}
