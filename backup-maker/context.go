package main

type ActionContext struct {
	url          string
	collectionId string
	authToken    string
	command      string
	timeout      int

	publicKeyPath    string
	privateKeyPath   string
	versionToRestore string

	gpg GPGOperationContext
}

func (that ActionContext) GetCommand() string {
	return that.command
}
