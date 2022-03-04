package main

import (
	"github.com/jessevdk/go-flags"
	"github.com/riotkit-org/backup-repository/collections"
	"github.com/riotkit-org/backup-repository/concurrency"
	"github.com/riotkit-org/backup-repository/config"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/db"
	"github.com/riotkit-org/backup-repository/http"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/riotkit-org/backup-repository/storage"
	"github.com/riotkit-org/backup-repository/users"
	log "github.com/sirupsen/logrus"
	"os"
)

type options struct {
	Help                 bool   `short:"h" long:"help" description:"Shows this help message"`
	Provider             string `short:"p" long:"provider" description:"Configuration provider. Choice: 'kubernetes', 'filesystem'" default:"kubernetes" env:"BR_CONFIG_PROVIDER"`
	Namespace            string `short:"n" long:"namespace" default:"backup-repository" description:"Kubernetes namespace to operate in"`
	EncodePasswordAction string `long:"encode-password" description:"Encode a password from CLI instead of running a server"`
	HashJWT              string `long:"hash-jwt" description:"Generate a hash from JWT"`
	DbHostname           string `long:"db-hostname" description:"Hostname for database connection" default:"localhost" env:"BR_DB_HOSTNAME"`
	DbUsername           string `long:"db-user" description:"Username for database connection" env:"BR_DB_USERNAME"`
	DbPassword           string `long:"db-password" description:"Password for database connection" env:"BR_DB_PASSWORD"`
	DbName               string `long:"db-name" description:"Database name inside a database" env:"BR_DB_NAME"`
	DbPort               int    `long:"db-port" description:"Database name inside a database" default:"5432" env:"BR_DB_PORT"`
	JwtSecretKey         string `long:"jwt-secret-key" short:"s" description:"Secret used for generating JSON Web Tokens for authentication" env:"BR_JWT_SECRET_KEY"`
	HealthCheckKey       string `long:"health-check-key" short:"k" description:"Secret key to access health check endpoint" env:"BR_HEALTH_CHECK_KEY"`
	Level                string `long:"log-level" description:"Log level" default:"debug" env:"BR_LOG_LEVEL"`
	StorageDriverUrl     string `long:"storage-url" description:"Storage driver url compatible with GO Cloud (https://gocloud.dev/howto/blob/)" env:"BR_STORAGE_DRIVER_URL"`
	IsGCS                bool   `long:"use-google-cloud" description:"If using Google Cloud Storage, then in --storage-url just type bucket name" env:"BR_USE_GOOGLE_CLOUD"`
}

func main() {
	var opts options
	p := flags.NewParser(&opts, flags.Default&^flags.HelpFlag)
	_, err := p.Parse()
	if err != nil {
		println(err)
		os.Exit(1)
	}
	logLevel, _ := log.ParseLevel(opts.Level)
	log.SetLevel(logLevel)
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

	//
	// Application services container is built here
	//

	configProvider, err := config.CreateConfigurationProvider(opts.Provider, opts.Namespace)
	if err != nil {
		log.Errorln("Cannot initialize Configuration Provider")
		log.Fatal(err)
	}
	dbDriver, err := db.CreateDatabaseDriver(opts.DbHostname, opts.DbUsername, opts.DbPassword, opts.DbName, opts.DbPort, "")
	if err != nil {
		log.Errorln("Cannot initialize database connection")
		log.Fatal(err)
	}
	locksService := concurrency.NewService(dbDriver)
	db.InitializeDatabase(dbDriver)

	usersService := users.NewUsersService(configProvider)
	gaService := security.NewService(dbDriver)
	collectionsService := collections.NewService(configProvider)
	storageService, storageError := storage.NewService(dbDriver, opts.StorageDriverUrl, opts.IsGCS)
	if storageError != nil {
		log.Errorln("Cannot initialize storage driver")
		log.Fatal(storageError)
	}

	ctx := core.ApplicationContainer{
		Db:              dbDriver,
		Config:          &configProvider,
		Users:           &usersService,
		GrantedAccesses: &gaService,
		JwtSecretKey:    opts.JwtSecretKey,
		HealthCheckKey:  opts.HealthCheckKey,
		Collections:     &collectionsService,
		Storage:         &storageService,
		Locks:           &locksService,
	}

	if err := http.SpawnHttpApplication(&ctx); err != nil {
		log.Errorf("Cannot spawn HTTP server: %v", err)
		os.Exit(1)
	}
}
