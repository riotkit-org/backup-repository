package health

import (
	"errors"
	"github.com/riotkit-org/backup-repository/pkg/collections"
	"github.com/riotkit-org/backup-repository/pkg/config"
	"github.com/riotkit-org/backup-repository/pkg/storage"
	"github.com/stretchr/testify/assert"
	"testing"
	"time"
)

func TestBackupWindowValidator_Validate(t *testing.T) {
	type test struct {
		windows       collections.BackupWindows
		storage       *storageMock
		now           time.Time
		expectedError string
	}

	referenceTime := time.Date(2021, 05, 01, 16, 0, 0, 0, time.UTC)

	variants := []test{
		// Every 30 minutes, +5 minutes for backup sending
		// Last copy sent: Current hour (hh) : 00 minutes
		// Fail: hh:00 is not between hh:30 - hh:35 range
		{
			collections.BackupWindows{
				createWindow("*/30 * * * *", "0h05m0s"),
			},
			&storageMock{
				storage.UploadedVersion{CreatedAt: referenceTime.Add(time.Minute * 5)},
				nil,
			},
			referenceTime,
			"previous backup was not executed in expected time slots: interval(*/30 * * * *) + 0h05m0s. Latest backup created at: 2021-05-01 16:05:00 +0000 UTC",
		},

		// No version found
		{
			collections.BackupWindows{
				createWindow("*/30 * * * *", "0h05m0s"),
			},
			&storageMock{
				storage.UploadedVersion{},
				errors.New("not found"),
			},
			referenceTime,
			"cannot find any backup in the collection: not found",
		},

		// Success case
		{
			collections.BackupWindows{
				createWindow("30 * * * *", "0h05m0s"),
			},
			&storageMock{
				storage.UploadedVersion{CreatedAt: referenceTime.Add(time.Minute * -26)}, // 15:34 (because referenceTime = 16:00)
				nil,
			},
			referenceTime,
			"",
		},
	}

	for _, variant := range variants {
		v := BackupWindowValidator{
			svc: variant.storage,
			c: &collections.Collection{
				Metadata: config.ObjectMetadata{Name: "some-name"},
				Spec: collections.Spec{
					Windows: variant.windows,
				},
			},
			nowFactory: func() time.Time {
				return variant.now
			},
		}

		err := v.Validate()

		if variant.expectedError != "" {
			assert.Equal(t, variant.expectedError, err.Error())
		} else {
			assert.Nil(t, err)
		}
	}
}

type storageMock struct {
	latestVersion storage.UploadedVersion
	err           error
}

func (m *storageMock) FindLatestVersion(collectionId string) (storage.UploadedVersion, error) {
	return m.latestVersion, m.err
}

func createWindow(from string, duration string) collections.BackupWindow {
	b, _ := collections.NewBackupWindow(from, duration)
	return b
}
