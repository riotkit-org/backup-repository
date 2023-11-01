package health

import (
	"fmt"
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/pkg/collections"
	"github.com/riotkit-org/backup-repository/pkg/storage"
	"strings"
	"time"
)

type BackupWindowValidator struct {
	svc        StorageInterface
	c          *collections.Collection
	nowFactory func() time.Time
}

func (v BackupWindowValidator) Validate() error {
	// Backup Windows are optional
	if len(v.c.Spec.Windows) == 0 {
		return nil
	}

	latest, err := v.svc.FindLatestVersion(v.c.GetId())
	if err != nil {
		return errors.Wrap(err, "cannot find any backup in the collection")
	}

	allowedSlots := ""
	now := v.nowFactory()

	for _, window := range v.c.Spec.Windows {
		matches, err := window.IsInPreviousWindowTimeSlot(now, latest.CreatedAt)

		if err != nil {
			return errors.New(fmt.Sprintf("failed to calculate previous run for window '%v' - %v", window, err))
		}
		if matches {
			return nil
		}

		// check if we are now in a timeslot (which could mean that the backup is in-progress)
		if matches, _ := window.IsInPreviousWindowTimeSlot(now, now); matches {
			return nil
		}

		allowedSlots += fmt.Sprintf(", interval(%v) + %v", window.From, window.Duration)
	}

	return errors.Errorf("previous backup was not executed in expected time slots: %v. Latest backup created at: %s", strings.Trim(allowedSlots, ", "), latest.CreatedAt)
}

func NewBackupWindowValidator(svc *storage.Service, c *collections.Collection) BackupWindowValidator {
	return BackupWindowValidator{svc, c, func() time.Time { return time.Now() }}
}
