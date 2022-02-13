package security

import (
	"github.com/stretchr/testify/assert"
	"testing"
	"time"
)

func TestComparePassword_WorksForPasswordsComparedShortly(t *testing.T) {
	hash, _ := CreateHashFromPassword("riotkit")
	result, _ := ComparePassword("riotkit", hash)

	assert.True(t, result, "Expected that hashed 'riotkit' password would be possible to compare")
}

// TestComparePassword_WorksForPasswordsComparedIn10Seconds checks in at least 10s interval because
// hashing algorithm has a time-based comparison
func TestComparePassword_WorksForPasswordsComparedIn10Seconds(t *testing.T) {
	hash, _ := CreateHashFromPassword("riotkit")
	result, _ := ComparePassword("riotkit", hash)

	time.Sleep(time.Second * 10)

	assert.True(t, result, "Expected that hashed 'riotkit' password would be possible to compare")
}
