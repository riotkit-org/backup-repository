package context

import (
	"github.com/stretchr/testify/assert"
	"os/exec"
	"testing"
)

// Success scenario
func TestCreateGPGContext(t *testing.T) {
	ctx, err := CreateGPGContext("../resources/test/gpg-key.asc",
		"../resources/test/gpg-key.asc",
		"riotkit",
		"example@riotkit.org",
		true)

	ctx.CleanUp()
	assert.Nil(t, err)
}

// Import path is not valid. Error message should be in stdout/stderr, in error there could be exit status just
func TestCreateGPGContext_InvalidKeyPath(t *testing.T) {
	ctx, err := CreateGPGContext("invalid-path",
		"invalid-path",
		"riotkit",
		"example@riotkit.org",
		true)

	ctx.CleanUp()
	assert.Equal(t, "Cannot import key, error: exit status 2", err.Error())
}

// Encryption is DISABLED as no keys were specified
func TestCreateGPGContext_DisabledEncryption(t *testing.T) {
	_, err := CreateGPGContext("",
		"",
		"riotkit",
		"example@riotkit.org",
		true)

	assert.Nil(t, err)
}

// Check that directory and processes are cleaned up (path on disk + gpg-agent process)
func TestGPGOperationContext_CleanUp(t *testing.T) {
	ctx, _ := CreateGPGContext("../resources/test/gpg-key.asc",
		"../resources/test/gpg-key.asc",
		"riotkit",
		"example@riotkit.org",
		true)

	assert.DirExists(t, ctx.Path) // GPG temporary directory
	ctx.CleanUp()

	// GPG temporary directory
	assert.NoDirExists(t, ctx.Path)

	proc := exec.Command("/bin/bash", "-c", "ps aux |grep %v | grep -v grep")
	out, _ := proc.Output()
	assert.NotContains(t, string(out), "gpg-agent")
}
