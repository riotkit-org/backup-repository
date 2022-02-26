package storage

import (
	"fmt"
	"github.com/riotkit-org/backup-repository/collections"
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestNewFifoRotationStrategy_MutationTest(t *testing.T) {
	collectionFirst := collections.Collection{}
	versionsFirst := []UploadedVersion{
		{Filename: "1.1"},
	}

	collectionSecond := collections.Collection{}
	versionsSecond := []UploadedVersion{
		{Filename: "2.1"},
	}

	// one object from factory will not impact second
	strategyFirst := NewFifoRotationStrategy(&collectionFirst, versionsFirst)
	strategySecond := NewFifoRotationStrategy(&collectionSecond, versionsSecond)

	strategyFirst.collection.Metadata.Name = "first"
	strategySecond.collection.Metadata.Name = "second"

	assert.NotEqual(t, collectionFirst.Metadata.Name, collectionSecond.Metadata.Name)
	assert.NotEqual(t, strategyFirst, strategySecond)
	assert.NotEqual(t, &strategyFirst, &strategySecond)
}

func TestGetVersionsThatShouldBeDeletedIfThisVersionUploaded(t *testing.T) {
	collection := collections.Collection{}

	// given we have a collection with 5 backups already
	versions := []UploadedVersion{
		{VersionNumber: 1},
		{VersionNumber: 2},
		{VersionNumber: 3},
		{VersionNumber: 4},
		{VersionNumber: 5},
	}

	strategyFirst := NewFifoRotationStrategy(&collection, versions)

	// multiple test cases
	matrix := make(map[int]string)
	// matrix[maxBackupsCount] = expectedVersionNumbersToBeDeleted
	matrix[1] = "1,2,3,4,5,"
	matrix[2] = "1,2,3,4,"
	matrix[3] = "1,2,3,"
	matrix[4] = "1,2,"
	matrix[5] = "1,"

	for maxBackupsCount, expectedResult := range matrix {
		collection.Spec.MaxBackupsCount = maxBackupsCount
		toDelete := strategyFirst.GetVersionsThatShouldBeDeletedIfThisVersionUploaded(UploadedVersion{})
		toDeleteAsStr := ""

		for _, version := range toDelete {
			toDeleteAsStr += fmt.Sprintf("%v", version.VersionNumber) + ","
		}

		assert.Equal(t, expectedResult, toDeleteAsStr)
	}
}
