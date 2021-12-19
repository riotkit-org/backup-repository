package context

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
func (that ActionContext) GetCommand() string {
	if !that.Gpg.enabled(that.ActionType) {
		return that.Command
	}

	if that.ActionType == "make" {
		return that.Command + " | " + that.Gpg.GetEncryptionCommand()
	}

	return that.Gpg.GetDecryptionCommand() + " | " + that.Command
}

func (that ActionContext) ShouldShowStdout() bool {
	return that.LogLevel >= 5
}
