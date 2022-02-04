package main

import (
	"github.com/riotkit-org/backup-repository/config"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/http"
	"github.com/riotkit-org/backup-repository/users"
	"log"
)

func main() {
	// todo: Add argparsing
	//       --provider=kubernetes
	//       --encode-password=...

	configProvider, err := config.CreateConfigurationProvider("kubernetes")
	if err != nil {
		log.Fatal(err)
	}

	ctx := core.ApplicationContainer{
		Config: configProvider,
		Users:  users.NewUsersService(configProvider),
	}

	// todo: First thread - HTTP
	// todo: Second thread - configuration changes watcher
	//       Notice: Fork configuration objects on each request? Or do not allow updating, when any request is pending?
	http.SpawnHttpApplication(ctx)

	// todo: Add commandline arg --encode-password=... to allow password hashing from commandline (should not run whole application)
}