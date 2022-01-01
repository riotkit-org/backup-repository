package client

import (
	"errors"
	"fmt"
	ctx "github.com/riotkit-org/backup-repository/backup-maker/context"
	log "github.com/sirupsen/logrus"
	"io"
	"io/ioutil"
	"net/http"
	"os"
	"os/exec"
	"time"
)

func Download(context ctx.ActionContext, client HTTPClient) (io.ReadCloser, error) {
	url := context.Url + fmt.Sprintf("/api/stable/repository/collection/%v/version/%v", context.CollectionId, context.VersionToRestore)
	log.Infof("Downloading: %v", url)

	req, _ := http.NewRequest("GET", url, nil)
	client.SetTimeout(context.Timeout)
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

func DownloadBackupIntoStream(context ctx.ActionContext, writer io.Writer, client HTTPClient) error {
	log.Debugf("Downloading %v and copying into io.Writer stream", context.CollectionId)

	buffer, httpErr := Download(context, client)
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

func DownloadBackupIntoProcessStdin(context ctx.ActionContext, command string, client HTTPClient) error {
	log.Debugf("Using command stdin as writer stream: `%v`", context.GetPrintableCommand(command))

	cmd := exec.Command("/bin/bash", GetShellCommand(context.GetCommand(command))...)
	stdin, _ := cmd.StdinPipe()
	cmd.Stderr = os.Stderr
	cmd.Stdout = os.Stdout

	if err := cmd.Start(); err != nil {
		log.Errorf("Cannot start process: %v", err)
		return err
	}

	if downloadErr := DownloadBackupIntoStream(context, stdin, client); downloadErr != nil {
		log.Errorf("Cannot download - fetching error or shell process error: %v", downloadErr)
		_ = stdin.Close()
		_ = cmd.Process.Kill()

		return downloadErr
	}

	_ = stdin.Close()

	var timer *time.Timer
	var isErr error = nil

	timer = time.AfterFunc(time.Second*time.Duration(context.Timeout), func() {
		if err := gracefullyKillProcess(cmd); err != nil {
			log.Errorf("Cannot end process: %v. Exit Code: %v", err, cmd.ProcessState.ExitCode())
			isErr = err
		}
	})

	if waitErr := cmd.Wait(); waitErr != nil {
		log.Error("Process finished with error")
		return waitErr
	}
	timer.Stop()

	return isErr
}

func DownloadIntoFile(context ctx.ActionContext, targetFilePath string, client HTTPClient) error {
	log.Debugf("Downloading %v into file %v", context.CollectionId, targetFilePath)

	if err := DownloadBackupIntoProcessStdin(context, fmt.Sprintf("cat - > %v", targetFilePath), client); err != nil {
		return err
	}

	return nil
}
