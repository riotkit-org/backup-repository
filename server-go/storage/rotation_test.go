package storage

import (
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
