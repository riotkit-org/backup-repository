package context

import (
	"github.com/stretchr/testify/assert"
	"testing"
)

// Verify that GPG command will be added
func TestActionContext_GetCommand_WithGPG(t *testing.T) {
	ctx := ActionContext{}
	ctx.Command = "ps aux"
	ctx.ActionType = "make"
	ctx.Gpg = GPGOperationContext{
		PublicKeyPath:    "/path/to/key",
		PrivateKeyPath:   "/path/to/key",
		Passphrase:       "riotkit",
		Recipient:        "riotkit@riseup.net",
		Path:             "/path",
		ShouldShowOutput: false,
	}

	assert.Equal(t, "ps aux | gpg --homedir='/path' --encrypt --always-trust --recipient='riotkit@riseup.net' --armor --batch --yes", ctx.GetCommand(""))
}

// When doing:
//   - make: The command output is piped into GPG
//   - restore/download: The output comes from HTTP into GPG and then into the RESTORE PROCESS using STDIN
func TestActionContext_GetCommand_WithGPG_RestoreActionPlacesPipeAtRightSide(t *testing.T) {
	ctx := ActionContext{}
	ctx.Command = "tar xvf -"
	ctx.ActionType = "restore"
	ctx.Gpg = GPGOperationContext{
		PublicKeyPath:    "/path/to/key",
		PrivateKeyPath:   "/path/to/key",
		Passphrase:       "riotkit",
		Recipient:        "riotkit@riseup.net",
		Path:             "/path",
		ShouldShowOutput: false,
	}

	assert.Equal(t, "gpg --homedir='/path' --decrypt --recipient='riotkit@riseup.net' --armor --passphrase='riotkit' --batch --yes --pinentry-mode loopback --verbose | tar xvf -", ctx.GetCommand(""))
}

// Check that credentials will be erased
func TestActionContext_GetPrintableCommand_WithGPG(t *testing.T) {
	ctx := ActionContext{}
	ctx.Command = "ps aux"
	ctx.ActionType = "restore"
	ctx.Gpg = GPGOperationContext{
		PublicKeyPath:    "/path/to/key",
		PrivateKeyPath:   "/path/to/key",
		Passphrase:       "my-secret",
		Recipient:        "riotkit@riseup.net",
		Path:             "/path",
		ShouldShowOutput: false,
	}

	assert.Contains(t, ctx.GetPrintableCommand(""), "--passphrase='***'")
	assert.NotContains(t, ctx.GetPrintableCommand(""), "--passphrase='my-secret'")
}

// Allow creating backups in plaintext
func TestActionContext_GetCommand_PlaintextWithoutEncryption(t *testing.T) {
	ctx := ActionContext{}
	ctx.Command = "tar -zcvf - ./"
	ctx.ActionType = "make"
	ctx.Gpg = GPGOperationContext{
		PublicKeyPath:    "",
		PrivateKeyPath:   "",
		Passphrase:       "riotkit",
		Recipient:        "riotkit@riseup.net",
		Path:             "/path",
		ShouldShowOutput: false,
	}

	assert.Equal(t, "tar -zcvf - ./", ctx.GetCommand(""))
}

func TestActionContext_ShouldShowCommandsOutput(t *testing.T) {
	ctx := ActionContext{}
	ctx.LogLevel = 5
	assert.True(t, ctx.ShouldShowCommandsOutput())

	ctx.LogLevel = 4
	assert.False(t, ctx.ShouldShowCommandsOutput())
}
