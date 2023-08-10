package collections

import (
	"github.com/stretchr/testify/assert"
	"testing"
	"time"
)

// When e.g. we are 5 minutes after previous run, then returns the closest run that just begun
func TestGetStartingDateOfPreviousScheduledRun_WhenTimeIsJustAfterPreviousRun(t *testing.T) {
	window := BackupWindow{}
	_ = window.UnmarshalJSON([]byte("{\"from\": \"*/30 * * * *\", \"duration\": \"1h\"}"))
	now, _ := time.Parse("2006-01-02 15:04:05", "2022-01-01 01:35:05") // 5 minutes after */30

	previousRunStartDate, _ := window.GetStartingDateOfPreviousScheduledRun(now)

	assert.Equal(t, "2022-01-01 01:30:00 +0000 UTC", previousRunStartDate.String())
}

// When it is just that moment, when date equals start date, then return that start date
func TestGetStartingDateOfPreviousScheduledRun_WhenTimeEqualsCurrentRun(t *testing.T) {
	window := BackupWindow{}
	_ = window.UnmarshalJSON([]byte("{\"from\": \"*/30 * * * *\", \"duration\": \"1h\"}"))
	now, _ := time.Parse("2006-01-02 15:04:05", "2022-01-01 01:30:00")

	previousRunStartDate, _ := window.GetStartingDateOfPreviousScheduledRun(now)

	assert.Equal(t, "2022-01-01 01:00:00 +0000 UTC", previousRunStartDate.String())
}

func TestIsInWindowNow(t *testing.T) {
	window := BackupWindow{}
	_ = window.UnmarshalJSON([]byte("{\"from\": \"*/30 * * * *\", \"duration\": \"10m\"}"))

	assert.True(t, isInWindowNow(window, "2022-01-01 01:30:01"))
	assert.True(t, isInWindowNow(window, "2022-01-01 01:39:00"))
	assert.True(t, isInWindowNow(window, "2022-01-01 01:39:59"))

	assert.False(t, isInWindowNow(window, "2022-01-01 01:45:34"))
}

func TestGenerateNextVersionFilename(t *testing.T) {
	c := Collection{Spec: Spec{FilenameTemplate: "zsp-net-pl-${version}.tar.gz"}}
	assert.Equal(t, "zsp-net-pl-1.tar.gz", c.GenerateNextVersionFilename(1))
}

func TestGenerateNextVersionFilenameReplacesOnlyOnce(t *testing.T) {
	c := Collection{Spec: Spec{FilenameTemplate: "zsp-net-pl-${version}.tar.gz${version}"}}
	assert.Equal(t, "zsp-net-pl-1.tar.gz${version}", c.GenerateNextVersionFilename(1))
}

func TestGetEstimatedDiskSpaceForFullCollectionInBytes(t *testing.T) {
	c := Collection{Spec: Spec{MaxBackupsCount: 30, MaxOneVersionSize: "50G"}}

	calc, _ := c.getEstimatedDiskSpaceForFullCollectionInBytes()
	assert.Equal(t, int64(1500*1024*1024*1024), calc) // 50GB * 30 = 1500 GB * 1024mb * 1024kb * 1024b = 1500 GB in bytes
}

// helper
func isInWindowNow(window BackupWindow, nowStr string) bool {
	now, _ := time.Parse("2006-01-02 15:04:05", nowStr)
	result, _ := window.IsInWindowNow(now)

	return result
}
