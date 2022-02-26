package health

import (
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/collections"
	"github.com/riotkit-org/backup-repository/storage"
)

type SumOfVersionsValidator struct {
	svc *storage.Service
}

func (v SumOfVersionsValidator) Validate(c *collections.Collection) error {
	var totalSize int64
	allActive, _ := v.svc.FindAllActiveVersionsFor(c.GetId())

	for _, version := range allActive {
		totalSize += version.Filesize
	}

	maxCollectionSize, _ := c.GetCollectionMaxSize()

	if totalSize > maxCollectionSize {
		return errors.Errorf("Summary of all files is %vb, while collection hard limit is %vb", totalSize, maxCollectionSize)
	}

	return nil
}

func NewSumOfVersionsValidator(svc *storage.Service) SumOfVersionsValidator {
	return SumOfVersionsValidator{svc}
}
