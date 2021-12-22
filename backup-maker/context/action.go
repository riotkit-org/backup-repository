package context

import "strings"

type ActionContext struct {
	Url          string
	CollectionId string
	AuthToken    string
	Command      string
	Timeout      int
	ActionType   string

	VersionToRestore string
	DownloadPath     string

	Gpg      GPGOperationContext
	LogLevel uint32
}

// GetCommand returns a valid command that includes GPG encryption/decryption (if using)
// this method is fully context aware, it understands if we are uploading or downloading a backup therefore
// if decryption or encryption is performed
func (that ActionContext) GetCommand(custom string) string {
	cmd := that.Command

	if custom != "" {
		cmd = custom
	}

	if !that.Gpg.enabled(that.ActionType) {
		return cmd
	}

	if that.ActionType == "make" {
		return cmd + " | " + that.Gpg.GetEncryptionCommand()
	}

	return that.Gpg.GetDecryptionCommand() + " | " + cmd
}

// GetPrintableCommand returns same command as in GetCommand(), but with erased credentials
// so the command could be logged or printed into the console
func (that ActionContext) GetPrintableCommand(custom string) string {
	return strings.ReplaceAll(that.GetCommand(custom), that.Gpg.Passphrase, "***")
}

func (that ActionContext) ShouldShowCommandsOutput() bool {
	return that.LogLevel >= 5
}
