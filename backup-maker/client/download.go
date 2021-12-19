package client

import (
	"errors"
	"fmt"
	ctx "github.com/riotkit-org/backup-repository/backup-maker/context"
	log "github.com/sirupsen/logrus"
	"io"
	"io/ioutil"
	"net/http"
	"os/exec"
	"time"
)

func Download(context ctx.ActionContext) (io.ReadCloser, error) {
	url := context.Url + fmt.Sprintf("/api/stable/repository/collection/%v/version/%v", context.CollectionId, context.VersionToRestore)
	log.Infof("Downloading: %v", url)

	client := http.Client{}
	req, _ := http.NewRequest("GET", url, nil)
	client.Timeout = time.Second * time.Duration(context.Timeout)
	req.Header.Set("Authorization", fmt.Sprintf("Bearer %v", context.AuthToken))

	response, reqError := client.Do(req)
	if reqError != nil {
		return nil, reqError
	}
	if response.StatusCode >= 299 {
		responseText, _ := ioutil.ReadAll(response.Body)

		log.Error(fmt.Sprintf("HTTP request ended with response %v, and response: %v",
			response.Status, responseText))

		return nil, errors.New(fmt.Sprintf("Invalid request. Got %v as response", response.Status))
	}

	return response.Body, nil
}

func DownloadBackupIntoStream(context ctx.ActionContext, writer io.Writer) error {
	log.Debugf("Downloading %v and copying into io.Writer stream", context.CollectionId)

	buffer, httpErr := Download(context)
	if httpErr != nil {
		log.Errorf("Cannot download to store in a file, HTTP error: %v", httpErr)
		return httpErr
	}
	_, copyErr := io.Copy(writer, buffer)
	if copyErr != nil {
		log.Errorf("File copy error: %v", copyErr)
		return copyErr
	}

	return nil
}

func DownloadBackupIntoProcessStdin(context ctx.ActionContext, command string) error {
	log.Debugf("Using command stdin as writer stream: `%v`", command)

	cmd := exec.Command("/bin/bash", "-c", command)
	stdin, _ := cmd.StdinPipe()
	if err := cmd.Start(); err != nil {
		log.Errorf("Cannot start process: %v", err)
		return err
	}

	if downloadErr := DownloadBackupIntoStream(context, stdin); downloadErr != nil {
		log.Errorf("Cannot download - fetching error or shell process error: %v", downloadErr)
		_ = stdin.Close()
		_ = cmd.Process.Kill()

		return downloadErr
	}

	_ = stdin.Close()
	if err := gracefullyKillProcess(cmd); err != nil {
		log.Errorf("Cannot end process: %v", err)
		return err
	}

	return nil
}

func DownloadIntoFile(context ctx.ActionContext, targetFilePath string) error {
	log.Debugf("Downloading %v into file %v", context.CollectionId, targetFilePath)

	if err := DownloadBackupIntoProcessStdin(context, fmt.Sprintf("cat - > %v", targetFilePath)); err != nil {
		return err
	}

	return nil
}
