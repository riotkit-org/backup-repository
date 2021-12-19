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

// gracefullyKillProcess attempts to clean up created process tree
// to avoid keeping zombie processes
func gracefullyKillProcess(cmd *exec.Cmd) error {
	var killErr error = nil

	log.Println("Stopping process")

	// protect against zombie processes
	for retry := 0; retry < 5; retry++ {
		killErr = cmd.Process.Kill()

		if killErr == nil {
			break
		}
		log.Print("Attempting to end backup process")
		time.Sleep(5 * time.Second)
	}

	// after multiple retries just kill all children processes with force
	if killErr != nil {
		log.Print("Cannot end main process, killing all children processes")
		proc := exec.Command("/bin/bash", "-c", fmt.Sprintf("kill -KILL -\"%v\"", cmd.Process.Pid))
		killErr = proc.Run()
	}

	if killErr != nil {
		return errors.New(
			fmt.Sprintf(
				"Cannot kill backup process with it's children processes after "+
					"successful upload. Watch out for zombie processes. %v", killErr))
	}

	return nil
}

// Upload is uploading bytes read from io.Reader stream into HTTP endpoint of Backup Repository server
func Upload(domainWithSchema string, collectionId string, authToken string, body io.Reader, timeout int) (string, string, error) {
	if timeout == 0 {
		timeout = int(time.Second * 60 * 20)
	}

	url := fmt.Sprintf("%v/api/stable/repository/collection/%v/backup", domainWithSchema, collectionId)
	log.Printf("Uploading to %v", url)

	client := http.Client{}
	req, err := http.NewRequest(
		"POST",
		url,
		body)

	client.Timeout = time.Second * 3600
	req.Header.Set("Authorization", fmt.Sprintf("Bearer %v", authToken))

	if err != nil {
		log.Println(err)
		return "", "", err
	}
	resp, err := client.Do(req)
	if err != nil {
		log.Println(err)
		return "", "", err
	}
	content, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		log.Println(err)
		return "", "", err
	}

	if resp.Status != "200 OK" {
		return resp.Status,
			string(content),
			errors.New(fmt.Sprintf("Request to server failed, server returned %v", string(content)))
	}

	return resp.Status, string(content), nil
}

// UploadFromCommandOutput pushes a stdout of executed command through HTTP endpoint of Backup Repository under specified domain
// Upload is used to perform HTTP POST request
func UploadFromCommandOutput(context ctx.ActionContext) error {
	log.Print("/bin/bash", "-c", context.GetCommand())
	cmd := exec.Command("/bin/bash", "-c", context.GetCommand())
	cmd.Stderr = os.Stderr
	stdout, pipeErr := cmd.StdoutPipe()
	if pipeErr != nil {
		log.Println(pipeErr)
		return pipeErr
	}

	log.Print("Starting cmd.Run()")
	execErr := cmd.Start()
	if execErr != nil {
		log.Println("Cannot start backup process ", execErr)
		return execErr
	}

	log.Printf("Starting Upload() for PID=%v", cmd.Process.Pid)
	status, out, uploadErr := Upload(context.Url, context.CollectionId, context.AuthToken, stdout, context.Timeout)
	if uploadErr != nil {
		log.Errorf("Status: %v, Out: %v", status, out)
		return uploadErr
	}

	killErr := gracefullyKillProcess(cmd)
	if killErr != nil {
		return killErr
	}

	log.Info("Version uploaded")

	return nil
}
