package main

type ActionContext struct {
	url          string
	collectionId string
	authToken    string
	command      string
	timeout      int

	publicKey        string
	privateKey       string
	versionToRestore string
}
