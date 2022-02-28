package storage

import (
	"github.com/riotkit-org/backup-repository/collections"
	"sort"
)

// RotationStrategy defines how old backups should be rotated
type RotationStrategy interface {
	// CanUpload returns nil if YES, error if NO
	CanUpload(version UploadedVersion) error

	// GetVersionsThatShouldBeDeletedIfThisVersionUploaded lists all the versions that should be deleted if a new version would be submitted
	GetVersionsThatShouldBeDeletedIfThisVersionUploaded(version UploadedVersion) []UploadedVersion
}

//
// FifoRotationStrategy implements a simple queue, first is appended, oldest will be deleted
//
type FifoRotationStrategy struct {
	collection       *collections.Collection
	existingVersions []UploadedVersion
}

// CanUpload RotationStrategy can decide if we can still upload
func (frs *FifoRotationStrategy) CanUpload(version UploadedVersion) error {
	return nil
}

// GetVersionsThatShouldBeDeletedIfThisVersionUploaded interface implementation that allows RotationStrategy to decide which versions should be deleted right now, when uploading a new version
func (frs *FifoRotationStrategy) GetVersionsThatShouldBeDeletedIfThisVersionUploaded(version UploadedVersion) []UploadedVersion {
	existingVersions := frs.existingVersions

	// nothing to do, there is still enough slots
	if len(existingVersions) < frs.collection.Spec.MaxBackupsCount {
		return []UploadedVersion{}
	}

	// order by version number DESCENDING
	sort.SliceStable(existingVersions, func(i, j int) bool {
		return existingVersions[i].VersionNumber < existingVersions[j].VersionNumber
	})

	toDelete := len(existingVersions) - frs.collection.Spec.MaxBackupsCount
	toDelete = toDelete + 1 // consider tha we are going to upload new version now

	// oldest element
	return existingVersions[0:toDelete]
}

// NewFifoRotationStrategy is a factorial method
func NewFifoRotationStrategy(collection *collections.Collection, existingVersions []UploadedVersion) *FifoRotationStrategy {
	return &FifoRotationStrategy{
		collection:       collection,
		existingVersions: existingVersions,
	}
}

// todo: implement "fifo-plus-older"
