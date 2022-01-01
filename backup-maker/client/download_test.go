package client

import (
	"github.com/riotkit-org/backup-repository/backup-maker/context"
	log "github.com/sirupsen/logrus"
	"github.com/stretchr/testify/assert"
	"io"
	"io/ioutil"
	"net/http"
	"os"
	"strings"
	"testing"
)

func createExampleContext() context.ActionContext {
	ctx := context.ActionContext{}
	ctx.Timeout = 5
	ctx.VersionToRestore = "v1"
	ctx.Url = "http://localhost"
	ctx.LogLevel = 5
	log.SetLevel(log.Level(ctx.LogLevel))

	return ctx
}

func prepareBuildDirectory() {
	_ = os.Mkdir("../.build", os.FileMode(0755))
}

func cleanUpFileIfExists(path string) {
	_ = os.Remove(path)
}

// TestDownload checks most basic case: No GPG encryption, no connection issues, HTTP 200 OK response
func TestDownload(t *testing.T) {
	client := new(HttpClientMock)
	client.mockedResponse = http.Response{
		StatusCode: 200,
		Body:       io.NopCloser(strings.NewReader("SOME GPG ENCRYPTED CONTENT THERE")),
	}

	// perform action
	ctx := createExampleContext()
	_ = DownloadBackupIntoProcessStdin(ctx, "cat - > /tmp/test-data", client)

	// verify result - there should be a valid content fetched from HTTP response into a file
	data, _ := ioutil.ReadFile("/tmp/test-data")
	assert.Equal(t, "SOME GPG ENCRYPTED CONTENT THERE", string(data))
}

func TestDownload_FailsWhenHTTPCodeIsNotSuccess(t *testing.T) {
	client := new(HttpClientMock)
	client.mockedResponse = http.Response{
		StatusCode: 403,
		Status:     "403 Not authorized",
		Body:       io.NopCloser(strings.NewReader("Not authorized")),
	}

	ctx := createExampleContext()
	err := DownloadBackupIntoProcessStdin(ctx, "cat - > /tmp/test-data", client)

	assert.Equal(t, "Invalid request. Got 403 Not authorized as response", err.Error())
}

func TestDownload_FailsWhenShellCommandFails(t *testing.T) {
	client := new(HttpClientMock)
	client.mockedResponse = http.Response{
		StatusCode: 200,
		Body:       io.NopCloser(strings.NewReader("SOME GPG ENCRYPTED CONTENT THERE")),
	}

	// perform action
	ctx := createExampleContext()
	err := DownloadBackupIntoProcessStdin(ctx, "/bin/false", client)

	assert.Equal(t, "exit status 1", err.Error())
}

func TestDownload_SuccessWithValidGPG(t *testing.T) {
	prepareBuildDirectory()
	cleanUpFileIfExists("../.build/TestDownload_SuccessWithValidGPG")

	// mock HTTP response, so it will return GPG encrypted file (main.go)
	testFile, _ := ioutil.ReadFile("../resources/test/example-encrypted-file")
	client := new(HttpClientMock)
	client.mockedResponse = http.Response{
		StatusCode: 200,
		Body:       io.NopCloser(strings.NewReader(string(testFile))),
	}

	ctx := createExampleContext()
	ctx.ActionType = "download"
	ctx.Gpg, _ = context.CreateGPGContext("", "../resources/test/gpg-key.asc", "riotkit", "test@riotkit.org", true)
	defer ctx.Gpg.CleanUp()

	_ = DownloadBackupIntoProcessStdin(ctx, "cat - > ../.build/TestDownload_SuccessWithValidGPG", client)

	decryptedResult, _ := ioutil.ReadFile("../.build/TestDownload_SuccessWithValidGPG")
	assert.Contains(t, string(decryptedResult), "package main") // the encrypted file is the main.go file
}
