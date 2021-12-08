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
	ctx.publicKey = *makeCmd.String("p", "public-key", &argparse.Options{Required: true, Help: "GPG public key"})

	// restore
	restoreCmd := parser.NewCommand("restore", "Restore a backup version")
	ctx.privateKey = *restoreCmd.String("p", "private-key", &argparse.Options{Required: true, Help: "GPG public key"})
	ctx.versionToRestore = *restoreCmd.String("s", "version", &argparse.Options{Required: false, Help: "Version number", Default: "latest"})

	ctx.url = *parser.String("u", "url", &argparse.Options{Required: true, Help: "e.g. https://backups.example.org"})
	ctx.collectionId = *parser.String("i", "collection-id", &argparse.Options{Required: true, Help: "aaaa-bbb-ccc-dddd"})
	ctx.authToken = *parser.String("t", "auth-token", &argparse.Options{Required: true, Help: "JWT token that allows to upload at least one file successfully"})
	ctx.command = *parser.String("c", "cmd", &argparse.Options{Required: true, Help: "Command to execute, which output will be captured and sent to server"})
	ctx.timeout = *parser.Int("", "timeout", &argparse.Options{Required: false, Help: "Connection and read timeout in summary", Default: 60 * 20})

	err := parser.Parse(os.Args)

	if err != nil {
		log.Fatal(err)
	}

	if makeCmd.Happened() {
		err = uploader.UploadFromCommandOutput(ctx.command, ctx.url, ctx.collectionId, ctx.authToken, ctx.timeout)
		if err != nil {
			log.Fatal(err)
		}
	} else if restoreCmd.Happened() {
		log.Println(ctx.publicKey, ctx.privateKey, ctx.versionToRestore)
	}
}
