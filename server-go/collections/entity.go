package collections

import (
	"encoding/json"
	"errors"
	"fmt"
	"github.com/labstack/gommon/bytes"
	"github.com/riotkit-org/backup-repository/config"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/riotkit-org/backup-repository/users"
	"github.com/robfig/cron/v3"
	"strings"
	"time"
)

type StrategySpec struct {
	KeepLastOlderNotMoreThan string `json:"keepLastOlderNotMoreThan"`
	MaxOlderCopies           int    `json:"maxOlderCopies"`
}

type BackupWindow struct {
	From     string `json:"from"`
	Duration string `json:"duration"`

	parsed         cron.Schedule
	parsedDuration time.Duration
}

// UnmarshalJSON performs a validation when decoding a JSON
func (b *BackupWindow) UnmarshalJSON(in []byte) error {
	v := struct {
		From     string `json:"from"`
		Duration string `json:"duration"`
	}{}

	if unmarshalErr := json.Unmarshal(in, &v); unmarshalErr != nil {
		return unmarshalErr
	}

	parser := cron.NewParser(cron.Minute | cron.Hour | cron.Dom | cron.Month | cron.DowOptional)
	err := errors.New("")
	b.parsed, err = parser.Parse(b.From)

	if err != nil {
		return errors.New(fmt.Sprintf("cannot parse Backup Window: %v. Error: %v", b.From, err))
	}

	return nil
}

func (b *BackupWindow) IsInWindowNow(current time.Time) (bool, error) {
	nextRun := b.parsed.Next(current)
	var startDate time.Time
	retries := 0

	// First calculate startDate run to get "START DATE" and calculate "END DATE"
	// because the library does not provide a "Previous" method unfortunately
	for true {
		retries = retries + 1
		startDate = current.Add(time.Minute * time.Duration(-1))

		if startDate.Format(time.RFC822) != nextRun.Format(time.RFC822) {
			break
		}

		// six months
		if retries > 60*24*30*6 {
			return false, errors.New("cannot find a previous date in the backup window")
		}
	}

	endDate := startDate.Add(b.parsedDuration)

	// previous run -> previous run + duration
	return current.After(startDate) && current.Before(endDate), nil
}

type BackupWindows []BackupWindow

type Spec struct {
	Description       string                     `json:"description"`
	FilenameTemplate  string                     `json:"filenameTemplate"`
	MaxBackupsCount   int                        `json:"maxBackupsCount"`
	MaxOneVersionSize string                     `json:"maxOneVersionSize"`
	MaxCollectionSize string                     `json:"maxCollectionSize"`
	Windows           BackupWindows              `json:"windows"`
	StrategyName      string                     `json:"strategyName"`
	StrategySpec      StrategySpec               `json:"strategySpec"`
	AccessControl     security.AccessControlList `json:"accessControl"`
}

type Collection struct {
	Metadata config.ObjectMetadata `json:"metadata"`
	Spec     Spec                  `json:"spec"`
}

func (c Collection) CanUploadToMe(user *users.User) bool {
	if user.Spec.Roles.HasRole(security.RoleBackupUploader) {
		return true
	}

	for _, permitted := range c.Spec.AccessControl {
		if permitted.UserName == user.Metadata.Name && permitted.Roles.HasRole(security.RoleBackupUploader) {
			return true
		}
	}

	return false
}

func (c *Collection) GenerateNextVersionFilename(version int) string {
	return strings.Replace(c.Spec.FilenameTemplate, "${version}", fmt.Sprintf("%v", version), 1)
}

// getEstimatedDiskSpaceForFullCollectionInBytes returns a calculation how many disk space would be required to store all versions (excluding extra disk space)
// in ideal case it would be: MaxBackupsCount * MaxOneVersionSize
func (c *Collection) getEstimatedDiskSpaceForFullCollectionInBytes() (int64, error) {
	maxVersionSizeInBytes, err := c.GetMaxOneVersionSizeInBytes()
	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot calculate estimated collection size: %v", err))
	}
	return int64(c.Spec.MaxBackupsCount) * maxVersionSizeInBytes, nil
}

func (c *Collection) GetMaxOneVersionSizeInBytes() (int64, error) {
	return bytes.Parse(c.Spec.MaxOneVersionSize)
}

// GetEstimatedCollectionExtraSpace returns total space that can be extra allocated in case, when a single version exceeds its limit. Returned value is estimated, does not include real state.
func (c *Collection) GetEstimatedCollectionExtraSpace() (int64, error) {
	estimatedStandardCollectionSize, err := c.getEstimatedDiskSpaceForFullCollectionInBytes()
	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot calculate GetEstimatedCollectionExtraSpace(): %v", err))
	}
	maxCollectionSizeInBytes, err := c.getMaxCollectionSizeInBytes()
	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot calculate GetEstimatedCollectionExtraSpace(): %v", err))
	}
	return maxCollectionSizeInBytes - estimatedStandardCollectionSize, nil
}

func (c *Collection) getMaxCollectionSizeInBytes() (int64, error) {
	return bytes.Parse(c.Spec.MaxCollectionSize)
}

func (c *Collection) GetId() string {
	return c.Metadata.Name
}
