package main

import (
	"fmt"
	"github.com/akamensky/argparse"
	"github.com/riotkit-org/backup-repository/backup-maker/client"
	"github.com/riotkit-org/backup-repository/backup-maker/context"
	log "github.com/sirupsen/logrus"
	"os"
)

func createContextFromArgumentParsing() context.ActionContext {
	var parser = argparse.NewParser("backupmaker", "Prints provided string to stdout")
	parser.ExitOnHelp(true)

	ctx := context.ActionContext{}

	// make
	makeCmd := parser.NewCommand("make", "Submit new backup version")
	publicKeyPath := makeCmd.String("k", "key", &argparse.Options{Required: false, Help: "GPG public or private key (required if using GPG)"})
	makeCmdRecipient := makeCmd.String("r", "recipient", &argparse.Options{Required: false, Help: "GPG recipient e-mail (required if using GPG). By default this e-mail SHOULD BE same as e-mail used when restoring/downloading backup."})
	makeCmdCommand := makeCmd.String("c", "cmd", &argparse.Options{Required: true, Help: "Command to execute, which output will be captured and sent to server"})
	makeCmdPassphrase := makeCmd.String("", "passphrase", &argparse.Options{Required: false, Help: "Secret passphrase for GPG"})

	// restore
	restoreCmd := parser.NewCommand("restore", "Restore a backup version")
	restoreCmdPrivateKey := restoreCmd.String("p", "private-key", &argparse.Options{Required: false, Help: "GPG public key"})
	restoreCmdCommand := restoreCmd.String("c", "cmd", &argparse.Options{Required: true, Help: "Command which should take downloaded file as stdin stream e.g. some tar, unzip, psql"})
	restoreCmdPassphrase := restoreCmd.String("", "passphrase", &argparse.Options{Required: false, Help: "Secret passphrase for GPG"})
	restoreCmdVersionToRestore := restoreCmd.String("s", "version", &argparse.Options{Required: false, Help: "Version number", Default: "latest"})
	restoreCmdRecipient := restoreCmd.String("r", "recipient", &argparse.Options{Required: false, Help: "GPG recipient e-mail (required if using GPG). By default this e-mail SHOULD BE same as e-mail used when restoring/downloading backup."})

	// download
	downloadCmd := parser.NewCommand("download", "Download a backup version")
	downloadCmdPrivateKey := downloadCmd.String("p", "private-key", &argparse.Options{Required: false, Help: "GPG public key. If not given, then an encrypted file will be saved"})
	downloadCmdDownloadPath := downloadCmd.String("", "save-path", &argparse.Options{Required: true, Default: "", Help: "Place where to save file instead of executing a restore command"})
	downloadCmdPassphrase := downloadCmd.String("", "passphrase", &argparse.Options{Required: false, Help: "Secret passphrase for GPG"})
	downloadCmdVersionToDownload := downloadCmd.String("s", "version", &argparse.Options{Required: false, Help: "Version number", Default: "latest"})
	downloadCmdRecipient := downloadCmd.String("r", "recipient", &argparse.Options{Required: false, Help: "GPG recipient e-mail (required if using GPG). By default this e-mail SHOULD BE same as e-mail used when restoring/downloading backup."})

	url := parser.String("u", "url", &argparse.Options{Required: true, Help: "e.g. https://backups.example.org"})
	collectionId := parser.String("i", "collection-id", &argparse.Options{Required: true, Help: "aaaa-bbb-ccc-dddd"})
	authToken := parser.String("t", "auth-token", &argparse.Options{Required: true, Help: "JWT token that allows to upload at least one file successfully"})
	timeout := parser.Int("", "timeout", &argparse.Options{Required: false, Help: "Connection and read timeout in summary", Default: 60 * 20})
	logLevelStr := parser.String("", "log-level", &argparse.Options{Required: false, Help: "Verbosity level: panic|fatal|error|warn|info|debug|trace", Default: "info"})

	err := parser.Parse(os.Args)
	logLevel, _ := log.ParseLevel(*logLevelStr)
	log.SetLevel(logLevel)

	ctx.ActionType = ""
	passphrase := ""
	recipient := ""

	// prepare context
	ctx.Gpg.PublicKeyPath = *publicKeyPath
	ctx.Url = *url
	ctx.CollectionId = *collectionId
	ctx.AuthToken = *authToken
	ctx.Timeout = *timeout
	ctx.LogLevel = uint32(logLevel)
	if downloadCmd.Happened() {
		ctx.ActionType = "download"
		ctx.VersionToRestore = *downloadCmdVersionToDownload
		ctx.DownloadPath = *downloadCmdDownloadPath
		ctx.Gpg.PrivateKeyPath = *downloadCmdPrivateKey
		recipient = *downloadCmdRecipient
		passphrase = *downloadCmdPassphrase
	} else if restoreCmd.Happened() {
		ctx.ActionType = "restore"
		ctx.VersionToRestore = *restoreCmdVersionToRestore
		ctx.Gpg.PrivateKeyPath = *restoreCmdPrivateKey
		ctx.Command = *restoreCmdCommand
		recipient = *restoreCmdRecipient
		passphrase = *restoreCmdPassphrase
	} else if makeCmd.Happened() {
		ctx.ActionType = "make"
		ctx.Command = *makeCmdCommand
		recipient = *makeCmdRecipient
		passphrase = *makeCmdPassphrase
	}

	if err != nil {
		fmt.Println(err)
		os.Exit(1)
	}

	// GPG
	ctx.Gpg, err = context.CreateGPGContext(ctx.Gpg.PublicKeyPath, ctx.Gpg.PrivateKeyPath, passphrase, recipient, ctx.ShouldShowStdout())

	if err != nil {
		ctx.Gpg.CleanUp()
		log.Fatalf("Fatal error happened when creating GPG context: %v", err)
	}

	return ctx
}

func triggerMakeAction(ctx context.ActionContext) error {
	if err := client.UploadFromCommandOutput(ctx); err != nil {
		return err
	}

	return nil
}

func triggerRestoreAction(ctx context.ActionContext) error {
	if restoreErr := client.DownloadBackupIntoProcessStdin(ctx, ctx.Command); restoreErr != nil {
		log.Error("Cannot restore backup")
		log.Error(restoreErr)
		return restoreErr
	}

	return nil
}

func triggerDownloadAction(ctx context.ActionContext) error {
	if downloadErr := client.DownloadIntoFile(ctx, ctx.DownloadPath); downloadErr != nil {
		log.Error("Cannot download file")
		log.Error(downloadErr)
		return downloadErr
	}

	return nil
}

func main() {
	ctx := createContextFromArgumentParsing()
	defer ctx.Gpg.CleanUp()

	log.Debugf("Action: %v", ctx.ActionType)
	var err error = nil

	// actions
	if ctx.ActionType == "make" {
		err = triggerMakeAction(ctx)
	} else if ctx.ActionType == "restore" {
		err = triggerRestoreAction(ctx)
	} else if ctx.ActionType == "download" {
		err = triggerDownloadAction(ctx)
	}

	if err != nil {
		ctx.Gpg.CleanUp()
		log.Fatal(err)
	}
}
