package main

import (
	"github.com/akamensky/argparse"
	"github.com/riotkit-org/backup-repository/backup-maker/uploader"
	log "github.com/sirupsen/logrus"
	"os"
)

func main() {
	var parser = argparse.NewParser("backupmaker", "Prints provided string to stdout")
	parser.ExitOnHelp(true)

	ctx := ActionContext{}

	// make
	makeCmd := parser.NewCommand("make", "Submit new backup version")
	publicKeyPath := makeCmd.String("p", "public-key", &argparse.Options{Required: true, Help: "GPG public key"})

	// restore
	restoreCmd := parser.NewCommand("restore", "Restore a backup version")
	privateKey := restoreCmd.String("p", "private-key", &argparse.Options{Required: true, Help: "GPG public key"})

	versionToRestore := restoreCmd.String("s", "version", &argparse.Options{Required: false, Help: "Version number", Default: "latest"})

	url := parser.String("u", "url", &argparse.Options{Required: true, Help: "e.g. https://backups.example.org"})
	collectionId := parser.String("i", "collection-id", &argparse.Options{Required: true, Help: "aaaa-bbb-ccc-dddd"})
	authToken := parser.String("t", "auth-token", &argparse.Options{Required: true, Help: "JWT token that allows to upload at least one file successfully"})
	command := parser.String("c", "cmd", &argparse.Options{Required: true, Help: "Command to execute, which output will be captured and sent to server"})
	timeout := parser.Int("", "timeout", &argparse.Options{Required: false, Help: "Connection and read timeout in summary", Default: 60 * 20})
	passphrase := parser.String("s", "passphrase", &argparse.Options{Required: true, Help: "Secret passphrase for GPG"})
	logLevel := parser.Int("", "log-level", &argparse.Options{Required: false, Help: "Verbosity level. Set '5' to debug", Default: 4})

	err := parser.Parse(os.Args)
	log.SetLevel(log.Level(*logLevel))

	// prepare context
	ctx.privateKeyPath = *privateKey
	ctx.publicKeyPath = *publicKeyPath
	ctx.versionToRestore = *versionToRestore
	ctx.url = *url
	ctx.collectionId = *collectionId
	ctx.authToken = *authToken
	ctx.command = *command
	ctx.timeout = *timeout

	if err != nil {
		log.Fatal(err)
	}

	// GPG
	ctx.gpg, err = CreateGPGContext(ctx.publicKeyPath, ctx.privateKeyPath, *passphrase)
	if err != nil {
		ctx.gpg.cleanUp()
		log.Fatalf("Fatal error happened when creating GPG context: %v", err)
	}

	// actions
	if makeCmd.Happened() {
		err = uploader.UploadFromCommandOutput(ctx.command, ctx.url, ctx.collectionId, ctx.authToken, ctx.timeout)
		ctx.gpg.cleanUp()

		if err != nil {
			log.Fatal(err)
		}
	} else if restoreCmd.Happened() {
		ctx.gpg.cleanUp()
	}
}
