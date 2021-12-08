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

	// make
	makeCmd := parser.NewCommand("make", "Submit new backup version")
	publicKey := makeCmd.String("p", "public-key", &argparse.Options{Required: true, Help: "GPG public key"})

	// restore
	restoreCmd := parser.NewCommand("restore", "Restore a backup version")
	privateKey := restoreCmd.String("p", "private-key", &argparse.Options{Required: true, Help: "GPG public key"})
	versionToRestore := restoreCmd.String("s", "version", &argparse.Options{Required: false, Help: "Version number", Default: "latest"})

	domain := parser.String("u", "url", &argparse.Options{Required: true, Help: "e.g. https://backups.example.org"})
	collectionId := parser.String("i", "collection-id", &argparse.Options{Required: true, Help: "aaaa-bbb-ccc-dddd"})
	authToken := parser.String("t", "auth-token", &argparse.Options{Required: true, Help: "JWT token that allows to upload at least one file successfully"})
	command := parser.String("c", "cmd", &argparse.Options{Required: true, Help: "Command to execute, which output will be captured and sent to server"})

	err := parser.Parse(os.Args)

	if err != nil {
		log.Fatal(err)
	}

	if makeCmd.Happened() {
		err = uploader.UploadFromCommandOutput(*command, *domain, *collectionId, *authToken)
		if err != nil {
			log.Fatal(err)
		}
	} else if restoreCmd.Happened() {
		log.Println(publicKey, privateKey, versionToRestore)
	}
}
