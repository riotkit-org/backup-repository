package client

import (
	"github.com/riotkit-org/backup-repository/backup-maker/context"
	"github.com/stretchr/testify/assert"
	"io"
	"io/ioutil"
	"net/http"
	"strings"
	"testing"
)

func createExampleContext() context.ActionContext {
	ctx := context.ActionContext{}
	ctx.Timeout = 5
	ctx.VersionToRestore = "v1"
	ctx.Url = "http://localhost"

	return ctx
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
