package security_test

import (
	"github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestNewUserIdentityFromString_WithAccessKey(t *testing.T) {
	identity := security.NewUserIdentityFromString("hello$161")

	assert.Equal(t, "hello", identity.Username)
	assert.Equal(t, "161", identity.AccessKeyName)
}

func TestNewUserIdentityFromString(t *testing.T) {
	identity := security.NewUserIdentityFromString("my-name-is-borat")

	assert.Equal(t, "my-name-is-borat", identity.Username)
	assert.Equal(t, "", identity.AccessKeyName)
}
