package context

import (
	"errors"
	"fmt"
	log "github.com/sirupsen/logrus"
	"io"
	"io/ioutil"
	"os"
	"os/exec"
)

type GPGOperationContext struct {
	PublicKeyPath  string
	PrivateKeyPath string
	Passphrase     string
	Recipient      string

	// dynamic
	Path             string
	ShouldShowOutput bool
}

// CreateGPGContext is a factory method that creates a GPG directory and imports keys
func CreateGPGContext(publicKeyPath string, privateKeyPath string, passphrase string, recipient string, shouldShowOutput bool) (GPGOperationContext, error) {
	ctx := GPGOperationContext{}
	ctx.PublicKeyPath = publicKeyPath
	ctx.PrivateKeyPath = privateKeyPath
	ctx.Passphrase = passphrase
	ctx.Recipient = recipient
	ctx.ShouldShowOutput = shouldShowOutput

	path, err := ioutil.TempDir("/tmp", "backup-repository-gpg")
	ctx.Path = path

	if err != nil {
		log.Printf("Cannot create temporary directory for GPG: '%v'", path)
		return ctx, err
	}

	if ctx.PublicKeyPath != "" || ctx.PrivateKeyPath != "" {
		if initErr := ctx.initializeGPGDirectory(); initErr != nil {
			return ctx, initErr
		}
		if importErr := ctx.importKeys(); importErr != nil {
			return ctx, errors.New(fmt.Sprintf("Cannot import key, error: %v", importErr))
		}
		ctx.printImportedKeys()
	} else {
		log.Warningln("GPG disabled (no keys configured)")
	}

	return ctx, nil
}

func (that GPGOperationContext) CleanUp() {
	log.Debugf("Cleaning up GPG directory at '%v'", that.Path)
	err := os.RemoveAll(that.Path)

	if err != nil {
		log.Fatalf("Cannot delete GPG directory at '%v'", that.Path)
	}

	_ = exec.Command("/bin/bash", "-c", fmt.Sprintf("ps axu | grep gpg-agent | grep %v | grep -v grep | awk '{print $2}' | xargs kill -9", that.Path)).Run()
}

// importKeys is importing PUBLIC and PRIVATE keys into local temporary keyring created on-the-fly
func (that GPGOperationContext) importKeys() error {
	log.Debugf("Importing GPG keys '%v', '%v'", that.PrivateKeyPath, that.PublicKeyPath)

	for _, keyPath := range []string{that.PrivateKeyPath, that.PublicKeyPath} {
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
		cmd.Env = []string{fmt.Sprintf("GNUPGHOME=%v", that.Path)}
		if that.ShouldShowOutput {
			cmd.Stdout = os.Stdout
			cmd.Stderr = os.Stderr
		}

		stdin, _ := cmd.StdinPipe()
		_ = cmd.Start()
		_, _ = io.WriteString(stdin, that.Passphrase)
		_ = stdin.Close()
		cmdErr := cmd.Wait()

		if cmdErr != nil {
			log.Errorf("Cannot import key '%v'. Check output placed above", keyPath)
			return cmdErr
		}
	}

	return nil
}

func (that GPGOperationContext) printImportedKeys() {
	log.Println("Imported keys:")
	cmd := exec.Command("gpg", "--list-secret-keys")
	cmd.Env = []string{fmt.Sprintf("GNUPGHOME=%v", that.Path)}
	cmd.Stdout = os.Stdout
	cmd.Stderr = os.Stderr
	_ = cmd.Run()
}

// initializeGPGDirectory creates a GPG temporary keyring used on-the-fly, then it gets automatically deleted
// by the application
func (that GPGOperationContext) initializeGPGDirectory() error {
	log.Println("Initializing GPG directory")
	initParamsFilePath := fmt.Sprintf("%v/.init-params", that.Path)

	// create parameters file
	_ = os.WriteFile(
		initParamsFilePath,
		[]byte("Key-Type: 1\nKey-Length: 2048\nSubkey-Type: 1\nSubkey-Length: 2048\nName-Real: Backup Maker\nName-Email: riotkit@localhost\nExpire-Date: 0\n"),
		0600,
	)

	cmd := exec.Command(
		"gpg",
		"--gen-key",
		"--passphrase-fd", "0",
		"--pinentry-mode", "loopback",
		"--batch", initParamsFilePath)

	cmd.Env = []string{fmt.Sprintf("GNUPGHOME=%v", that.Path)}
	stdin, err := cmd.StdinPipe()
	if that.ShouldShowOutput {
		cmd.Stdout = os.Stdout
		cmd.Stderr = os.Stderr
	}

	if err != nil {
		_ = cmd.Process.Kill()
		return err
	}

	runErr := cmd.Start()
	if runErr != nil {
		return runErr
	}

	log.Debugf("Writing Passphrase...")
	if _, writeErr := io.WriteString(stdin, that.Passphrase); writeErr != nil {
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

func (that GPGOperationContext) GetEncryptionCommand() string {
	if that.PublicKeyPath == "" && that.PrivateKeyPath == "" {
		log.Debug("No private key, no public key, no encryption then")
		return ""
	}

	return fmt.Sprintf("gpg --homedir='%v' --encrypt --always-trust --recipient='%v' --armor --batch --yes", that.Path, that.Recipient)
}

func (that GPGOperationContext) GetDecryptionCommand() string {
	if that.PrivateKeyPath == "" {
		log.Debug("No private key, no encryption")
		return ""
	}

	return fmt.Sprintf("gpg --homedir='%v' --decrypt --recipient='%v' --armor "+
		"--passphrase='%v' --batch --yes --pinentry-mode loopback --verbose",
		that.Path, that.Recipient, that.Passphrase)
}

func (that GPGOperationContext) Enabled(actionType string) bool {
	if actionType == "make" {
		return that.PublicKeyPath != ""
	}

	return that.PrivateKeyPath != "" || that.PublicKeyPath != ""
}
