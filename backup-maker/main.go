package main

import (
	"fmt"
	"github.com/akamensky/argparse"
	"github.com/riotkit-org/backup-repository/backup-maker/client"
	"github.com/riotkit-org/backup-repository/backup-maker/context"
	log "github.com/sirupsen/logrus"
	"os"
	"strconv"
)

func hasUsedEnvVariable(name string) bool {
	return os.Getenv(name) != ""
}

func shouldBeRequired(envName string) bool {
	if hasUsedEnvVariable(envName) {
		return false
	}

	return true
}

func createContextFromArgumentParsing() context.ActionContext {
	var parser = argparse.NewParser("backupmaker", "Performs backup & restore operations with addition of GPG encryption")
	parser.ExitOnHelp(true)

	ctx := context.ActionContext{}

	// make
	makeCmd := parser.NewCommand("make", "Submit new backup version")
	publicKeyPath := makeCmd.String("k", "key", &argparse.Options{Required: shouldBeRequired("BM_PUBLIC_KEY_PATH"), Help: "GPG public or private key (required if using GPG) [environment variable: BM_PUBLIC_KEY_PATH]"})
	makeCmdRecipient := makeCmd.String("r", "recipient", &argparse.Options{Required: false, Help: "GPG recipient e-mail (required if using GPG). By default this e-mail SHOULD BE same as e-mail used when restoring/downloading backup [environment variable: BM_RECIPIENT]"})
	makeCmdCommand := makeCmd.String("c", "cmd", &argparse.Options{Required: shouldBeRequired("BM_CMD"), Help: "Command to execute, which output will be captured and sent to server [environment variable: BM_CMD]"})
	makeCmdPassphrase := makeCmd.String("", "passphrase", &argparse.Options{Required: false, Help: "Secret passphrase for GPG [environment variable: BM_PASSPHRASE]"})

	// restore
	restoreCmd := parser.NewCommand("restore", "Restore a backup version")
	restoreCmdPrivateKey := restoreCmd.String("p", "private-key", &argparse.Options{Required: false, Help: "GPG private key. [environment variable: BM_PRIVATE_KEY_PATH]"})
	restoreCmdCommand := restoreCmd.String("c", "cmd", &argparse.Options{Required: shouldBeRequired("BM_CMD"), Help: "Command which should take downloaded file as stdin stream e.g. some tar, unzip, psql [environment variable: BM_CMD]"})
	restoreCmdPassphrase := restoreCmd.String("", "passphrase", &argparse.Options{Required: false, Help: "Secret passphrase for GPG [environment variable: BM_PASSPHRASE]"})
	restoreCmdVersionToRestore := restoreCmd.String("s", "version", &argparse.Options{Required: false, Help: "Version number [environment variable: BM_VERSION]", Default: "latest"})
	restoreCmdRecipient := restoreCmd.String("r", "recipient", &argparse.Options{Required: false, Help: "GPG recipient e-mail (required if using GPG). By default this e-mail SHOULD BE same as e-mail used when restoring/downloading backup [environment variable: BM_RECIPIENT]"})

	// download
	downloadCmd := parser.NewCommand("download", "Download a backup version")
	downloadCmdPrivateKey := downloadCmd.String("p", "private-key", &argparse.Options{Required: false, Help: "GPG private key. If not given, then an encrypted file will be saved [environment variable: BM_PUBLIC_KEY_PATH]"})
	downloadCmdDownloadPath := downloadCmd.String("", "save-path", &argparse.Options{Required: true, Default: "", Help: "Place where to save file instead of executing a restore command"})
	downloadCmdPassphrase := downloadCmd.String("", "passphrase", &argparse.Options{Required: false, Help: "Secret passphrase for GPG [environment variable: BM_PASSPHRASE]"})
	downloadCmdVersionToDownload := downloadCmd.String("s", "version", &argparse.Options{Required: false, Help: "Version number", Default: "latest"})
	downloadCmdRecipient := downloadCmd.String("r", "recipient", &argparse.Options{Required: false, Help: "GPG recipient e-mail (required if using GPG). By default this e-mail SHOULD BE same as e-mail used when restoring/downloading backup [environment variable: BM_RECIPIENT]"})

	url := parser.String("u", "url", &argparse.Options{Required: shouldBeRequired("BM_URL"), Help: "e.g. https://backups.example.org [environment variable: BM_URL]"})
	collectionId := parser.String("i", "collection-id", &argparse.Options{Required: shouldBeRequired("BM_COLLECTION_ID"), Help: "aaaa-bbb-ccc-dddd [environment variable: BM_COLLECTION_ID]"})
	authToken := parser.String("t", "auth-token", &argparse.Options{Required: shouldBeRequired("BM_AUTH_TOKEN"), Help: "JWT token that allows to upload at least one file successfully, [environment variable: BM_AUTH_TOKEN]"})
	timeout := parser.Int("", "timeout", &argparse.Options{Required: false, Help: "Connection and read timeout in summary [environment variable: BM_TIMEOUT]", Default: 60 * 20})
	logLevelStr := parser.String("", "log-level", &argparse.Options{Required: false, Help: "Verbosity level: panic|fatal|error|warn|info|debug|trace", Default: "info"})

	err := parser.Parse(os.Args)
	logLevel, _ := log.ParseLevel(*logLevelStr)
	log.SetLevel(logLevel)

	ctx.ActionType = ""
	passphrase := ""
	recipient := ""

	// prepare context
	ctx.Gpg.PublicKeyPath = *publicKeyPath // Public & Private keys are assigned there, but later will be re-assigned by factory method
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

	passphrase, recipient, ctx = overrideFromEnvironment(ctx, passphrase, recipient)

	// GPG
	ctx.Gpg, err = context.CreateGPGContext(ctx.Gpg.PublicKeyPath, ctx.Gpg.PrivateKeyPath, passphrase, recipient, ctx.ShouldShowCommandsOutput())

	if err != nil {
		ctx.Gpg.CleanUp()
		log.Fatalf("Fatal error happened when creating GPG context: %v", err)
	}

	return ctx
}

func overrideFromEnvironment(ctx context.ActionContext, passphrase string, recipient string) (string, string, context.ActionContext) {
	if hasUsedEnvVariable("BM_PUBLIC_KEY_PATH") {
		ctx.Gpg.PublicKeyPath = os.Getenv("BM_PUBLIC_KEY_PATH")
	}
	if hasUsedEnvVariable("BM_PRIVATE_KEY_PATH") {
		ctx.Gpg.PrivateKeyPath = os.Getenv("BM_PRIVATE_KEY_PATH")
	}
	if hasUsedEnvVariable("BM_PASSPHRASE") {
		passphrase = os.Getenv("BM_PASSPHRASE")
	}
	if hasUsedEnvVariable("BM_URL") {
		ctx.Url = os.Getenv("BM_URL")
	}
	if hasUsedEnvVariable("BM_AUTH_TOKEN") {
		ctx.AuthToken = os.Getenv("BM_AUTH_TOKEN")
	}
	if hasUsedEnvVariable("BM_COLLECTION_ID") {
		ctx.CollectionId = os.Getenv("BM_COLLECTION_ID")
	}
	if hasUsedEnvVariable("BM_TIMEOUT") {
		ctx.Timeout, _ = strconv.Atoi(os.Getenv("BM_TIMEOUT"))
	}
	if hasUsedEnvVariable("BM_RECIPIENT") {
		recipient = os.Getenv("BM_RECIPIENT")
	}
	if hasUsedEnvVariable("BM_CMD") {
		ctx.Command = os.Getenv("BM_CMD")
	}
	if hasUsedEnvVariable("BM_VERSION") {
		ctx.VersionToRestore = os.Getenv("BM_VERSION")
	}

	return passphrase, recipient, ctx
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
