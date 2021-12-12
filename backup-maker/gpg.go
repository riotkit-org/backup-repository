package main

import (
	"fmt"
	log "github.com/sirupsen/logrus"
	"io"
	"io/ioutil"
	"os"
	"os/exec"
)

type GPGOperationContext struct {
	publicKeyPath  string
	privateKeyPath string
	passphrase     string

	// dynamic
	path string
}

// CreateGPGContext is a factory method that creates a GPG directory and imports keys
func CreateGPGContext(publicKeyPath string, privateKeyPath string, passphrase string) (GPGOperationContext, error) {
	ctx := GPGOperationContext{}
	ctx.publicKeyPath = publicKeyPath
	ctx.privateKeyPath = privateKeyPath
	ctx.passphrase = passphrase

	path, err := ioutil.TempDir("/tmp", "backup-repository-gpg")
	ctx.path = path

	if err != nil {
		log.Printf("Cannot create temporary directory for GPG: '%v'", path)
		return ctx, err
	}

	if initErr := ctx.initializeGPGDirectory(); initErr != nil {
		return ctx, initErr
	}
	if importErr := ctx.importKeys(); importErr != nil {
		return ctx, importErr
	}

	return ctx, nil
}

func (that GPGOperationContext) cleanUp() {
	log.Debugf("Cleaning up GPG directory at '%v'", that.path)
	err := os.RemoveAll(that.path)

	if err != nil {
		log.Fatalf("Cannot delete GPG directory at '%v'", that.path)
	}
}

func (that GPGOperationContext) importKeys() error {
	log.Debugf("Importing GPG keys '%v', '%v'", that.privateKeyPath, that.publicKeyPath)

	for _, keyPath := range []string{that.privateKeyPath, that.publicKeyPath} {
		if keyPath == "" {
			continue
		}

		log.Printf("Importing key %v", keyPath)
		cmd := exec.Command(
			"gpg",
			"--passphrase-fd", "0",
			"--pinentry-mode", "loopback",
			"--import", keyPath,
		)
		cmd.Env = []string{fmt.Sprintf("GNUPGHOME=%v", that.path)}
		cmd.Stdout = os.Stdout
		cmd.Stderr = os.Stderr

		stdin, _ := cmd.StdinPipe()
		_ = cmd.Start()
		_, _ = io.WriteString(stdin, that.passphrase)
		_ = stdin.Close()
		cmdErr := cmd.Wait()

		if cmdErr != nil {
			log.Errorf("Cannot import key '%v'. Check output placed above", keyPath)
			return cmdErr
		}
	}

	return nil
}

func (that GPGOperationContext) initializeGPGDirectory() error {
	log.Println("Initializing GPG directory")
	initParamsFilePath := fmt.Sprintf("%v/.init-params", that.path)

	// create parameters file
	_ = os.WriteFile(
		initParamsFilePath,
		[]byte("Key-Type: 1\nKey-Length: 2048\nSubkey-Type: 1\nSubkey-Length: 2048\nName-Real: Backup Maker\nName-Email: riotkit@localhost\nExpire-Date: 0\n"),
		0600,
	)

	cmd := exec.Command(
		"gpg",
		"--passphrase-fd", "0",
		"--pinentry-mode", "loopback",
		"--gen-key",
		"--batch", initParamsFilePath)

	cmd.Env = []string{fmt.Sprintf("GNUPGHOME=%v", that.path)}
	stdin, err := cmd.StdinPipe()
	cmd.Stdout = os.Stdout
	cmd.Stderr = os.Stderr

	if err != nil {
		_ = cmd.Process.Kill()
		return err
	}

	runErr := cmd.Start()
	if runErr != nil {
		return runErr
	}

	log.Debugf("Writing passphrase...")
	if _, writeErr := io.WriteString(stdin, that.passphrase); writeErr != nil {
		_ = cmd.Process.Kill()
		return writeErr
	}

	_ = stdin.Close()
	waitErr := cmd.Wait()
	if waitErr != nil {
		_ = cmd.Process.Kill()
		return waitErr
	}

	log.Debugf("GPG initialized")
	return nil
}
