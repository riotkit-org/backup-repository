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

	b.From = v.From
	b.Duration = v.Duration

	parser := cron.NewParser(cron.Minute | cron.Hour | cron.Dom | cron.Month | cron.DowOptional)
	err := errors.New("")
	b.parsed, err = parser.Parse(b.From)

	if err != nil {
		return errors.New(fmt.Sprintf("cannot parse Backup Window: %v. Error: %v", b.From, err))
	}

	b.parsedDuration, err = time.ParseDuration(b.Duration)
	if err != nil {
		return errors.New(fmt.Sprintf("cannot parse Backup Window - duation parsing, Error: %v", err))
	}

	return nil
}

// IsInWindowNow checks if given time is between BackupWindow time slot (<-start--O--end->)
func (b *BackupWindow) IsInWindowNow(current time.Time) (bool, error) {
	startDate, err := b.GetStartingDateOfPreviousScheduledRun(current)
	if err != nil {
		return false, err
	}

	endDate := startDate.Add(b.parsedDuration)

	// previous run -> previous run + duration
	return current.After(startDate) && current.Before(endDate), nil
}

func (b *BackupWindow) GetStartingDateOfPreviousScheduledRun(current time.Time) (time.Time, error) {
	nextRun := b.parsed.Next(current)

	// check if next run is NOW
	possibleNextRunNow := current.Add(time.Second * time.Duration(-1))
	if b.parsed.Next(possibleNextRunNow).Format(time.RFC822) != nextRun.Format(time.RFC822) {
		nextRun = b.parsed.Next(possibleNextRunNow)
	}

	startDate := current
	retries := 0

	// First calculate startDate run to get "START DATE" and calculate "END DATE"
	// because the library does not provide a "Previous" method unfortunately
	for true {
		retries = retries + 1
		startDate = startDate.Add(time.Minute * time.Duration(-1))
		possiblePrevRun := b.parsed.Next(startDate)

		if possiblePrevRun.Format(time.RFC822) != nextRun.Format(time.RFC822) {
			return possiblePrevRun, nil
		}

		// 12 months
		if retries > 60*24*30*12 {
			return time.Time{}, errors.New("cannot find a previous date in the backup window")
		}
	}

	return time.Time{}, errors.New("unknown error while attempting to find start date for backup window")
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

// CanUploadToMe answers if user can add new versions to the collection
func (c Collection) CanUploadToMe(user *users.User) bool {
	return user.Spec.Roles.HasRole(security.RoleBackupUploader) || c.Spec.AccessControl.IsPermitted(user.Metadata.Name, security.RoleBackupUploader)
}

// CanDownloadFromMe answers if user can download versions to this collection
func (c Collection) CanDownloadFromMe(user *users.User) bool {
	return user.Spec.Roles.HasRole(security.RoleBackupDownloader) || c.Spec.AccessControl.IsPermitted(user.Metadata.Name, security.RoleBackupDownloader)
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

func (c Collection) GetGlobalIdentifier() string {
	return "collection:" + c.GetId()
}
