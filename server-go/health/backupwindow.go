package health

import (
	"fmt"
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/collections"
	"github.com/riotkit-org/backup-repository/storage"
	"strings"
	"time"
)

type BackupWindowValidator struct {
	svc *storage.Service
}

func (v BackupWindowValidator) Validate(c *collections.Collection) error {
	latest, err := v.svc.FindLatestVersion(c.GetId())
	if err != nil {
		return err
	}

	allowedSlots := ""
	now := time.Now()

	// Backup Windows are optional
	if len(c.Spec.Windows) == 0 {
		return nil
	}

	for _, window := range c.Spec.Windows {
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

	return errors.Errorf("previous backup was not executed in expected time slots: %v", strings.Trim(allowedSlots, ", "))
}

func NewBackupWindowValidator(svc *storage.Service) BackupWindowValidator {
	return BackupWindowValidator{svc}
}
