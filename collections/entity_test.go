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

// helper
func isInWindowNow(window BackupWindow, nowStr string) bool {
	now, _ := time.Parse("2006-01-02 15:04:05", nowStr)
	result, _ := window.IsInWindowNow(now)

	return result
}
