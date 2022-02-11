package main

import (
	"github.com/jessevdk/go-flags"
	"github.com/riotkit-org/backup-repository/config"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/db"
	"github.com/riotkit-org/backup-repository/http"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/riotkit-org/backup-repository/users"
	log "github.com/sirupsen/logrus"
	"os"
)

type options struct {
	Help                 bool   `short:"h" long:"help" description:"Shows this help message"`
	Provider             string `short:"p" long:"provider" description:"Configuration provider. Choice: 'kubernetes', 'filesystem'" default:"kubernetes"`
	EncodePasswordAction string `long:"encode-password" description:"Encode a password from CLI instead of running a server"`
	HashJWT              string `long:"hash-jwt" description:"Generate a hash from JWT"`
	DbHostname           string `long:"db-hostname" description:"Hostname for database connection" default:"localhost"`
	DbUsername           string `long:"db-user" description:"Username for database connection"`
	DbPassword           string `long:"db-password" description:"Password for database connection"`
	DbName               string `long:"db-name" description:"Database name inside a database"`
	DbPort               int    `long:"db-port" description:"Database name inside a database" default:"5432"`
}

func main() {
	var opts options
	p := flags.NewParser(&opts, flags.Default&^flags.HelpFlag)
	_, err := p.Parse()
	if err != nil {
		os.Exit(1)
	}
	if opts.Help {
		p.WriteHelp(os.Stdout)
		os.Exit(0)
	}
	// allows encoding passwords from CLI to make the configmap creation easier
	if opts.EncodePasswordAction != "" {
		hash, _ := security.CreateHashFromPassword(opts.EncodePasswordAction)
		println(hash)
		os.Exit(0)
	}
	// allows to hash a JWT, to be later used in comparison in `kind: GrantedAccess`
	if opts.HashJWT != "" {
		println(security.HashJWT(opts.HashJWT))
		os.Exit(0)
	}

	logLevel, _ := log.ParseLevel("debug")
	log.SetLevel(logLevel)

	configProvider, err := config.CreateConfigurationProvider(opts.Provider)
	if err != nil {
		log.Errorln("Cannot initialize Configuration Provider")
		log.Fatal(err)
	}
	dbDriver, err := db.CreateDatabaseDriver(opts.DbHostname, opts.DbUsername, opts.DbPassword, opts.DbName, opts.DbPort, "")
	if err != nil {
		log.Errorln("Cannot initialize database connection")
		log.Fatal(err)
	}
	db.InitializeDatabase(dbDriver)

	ctx := core.ApplicationContainer{
		Config:          configProvider,
		Users:           users.NewUsersService(configProvider),
		GrantedAccesses: security.NewService(dbDriver),
	}

	// todo: First thread - HTTP
	// todo: Second thread - configuration changes watcher
	//       Notice: Fork configuration objects on each request? Or do not allow updating, when any request is pending?
	http.SpawnHttpApplication(ctx)

	// todo: Add commandline arg --encode-password=... to allow password hashing from commandline (should not run whole application)
}
