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
	"gorm.io/gorm"
	"os"
	"time"
)

type options struct {
	Help                 bool   `short:"h" long:"help" description:"Shows this help message"`
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

	// storage timeouts
	StorageHealthTimeout string `long:"storage-health-timeout" description:"Maximum allowed storage ping" default:"5s" env:"BR_STORAGE_HEALTH_TIMEOUT"`
	StorageIOTimeout     string `long:"storage-io-timeout" description:"Maximum time the server can read/write to storage for a single file. WARNING! If you have very large files you need to consider to increase this value." default:"2h" env:"BR_STORAGE_IO_TIMEOUT"`

	// http timeouts
	UploadTimeout   string `long:"http-upload-timeout" description:"HTTP upload endpoint timeout" default:"180m" env:"BR_HTTP_UPLOAD_TIMEOUT"`
	DownloadTimeout string `long:"http-download-timeout" description:"HTTP download endpoint timeout" default:"180m" env:"BR_HTTP_DOWNLOAD_TIMEOUT"`

	// request rate limit
	DefaultRPS          int16 `long:"rate-default-limit" description:"Request rate limit for all endpoints (except those that have it's dedicated limit). Unit: requests per second" default:"5"`
	AuthRPM             int16 `long:"rate-auth-limit" description:"Request rate limit for login/authentication endpoints. Unit: requests per minute" default:"10"`
	CollectionHealthRPM int16 `long:"rate-collection-health-limit" description:"Request rate limit for collection's /health endpoint. Unit: requests per minute" default:"10"`
	ServerHealthRPM     int16 `long:"rate-server-health-limit" description:"Request rate limit for server's /health and /ready endpoints. Unit: requests per minute. WARNING: Be careful in Kubernetes as Kube API also can hit this rate limit and restart your service!" default:"160"`

	ListenAddr string `long:"listen" description:"Address to listen on with HTTP API (e.g. :8080)" default:":8080"`

	// Configuration provider
	Provider        string `short:"p" long:"provider" description:"Configuration provider. Choice: 'kubernetes', 'filesystem'" default:"kubernetes" env:"BR_CONFIG_PROVIDER"`
	ConfigLocalPath string `long:"config-local-path" description:"Configuration path (if using --provider=filesystem)" default:"~/.backup-repository" env:"BR_CONFIG_LOCAL_PATH"`
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

	configProvider, err := config.CreateConfigurationProvider(opts.Provider, opts.Namespace, opts.ConfigLocalPath)
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

	ctx := core.ApplicationContainer{
		Db:              dbDriver,
		Config:          &configProvider,
		Users:           &usersService,
		GrantedAccesses: &gaService,
		JwtSecretKey:    opts.JwtSecretKey,
		HealthCheckKey:  opts.HealthCheckKey,
		Collections:     &collectionsService,
		Storage:         createStorage(dbDriver, &opts),
		Locks:           &locksService,

		// timeouts
		UploadTimeout:   toDurationOrFatal(opts.UploadTimeout),
		DownloadTimeout: toDurationOrFatal(opts.DownloadTimeout),

		// request limit rates
		DefaultRPS:          opts.DefaultRPS,
		AuthRPM:             opts.AuthRPM,
		CollectionHealthRPM: opts.CollectionHealthRPM,
		ServerHealthRPM:     opts.ServerHealthRPM,
	}

	if err := http.SpawnHttpApplication(&ctx, opts.ListenAddr); err != nil {
		log.Errorf("Cannot spawn HTTP server: %v", err)
		os.Exit(1)
	}
}

func createStorage(dbDriver *gorm.DB, opts *options) *storage.Service {
	healthTimeout, storageTimeoutErr := time.ParseDuration(opts.StorageHealthTimeout)
	if storageTimeoutErr != nil {
		log.Errorln("Cannot parse --storage-health-timeout duration")
		log.Fatal(storageTimeoutErr)
	}

	ioTimeout, ioTimeoutErr := time.ParseDuration(opts.StorageIOTimeout)
	if ioTimeoutErr != nil {
		log.Errorln("Cannot parse --storage-io-timeout duration")
		log.Fatal(ioTimeoutErr)
	}

	log.Debugf("Creating storage with ioTimeout=%v, healthTimeout=%v", ioTimeout, healthTimeout)

	storageService, storageError := storage.NewService(dbDriver, opts.StorageDriverUrl, opts.IsGCS, healthTimeout, ioTimeout)
	if storageError != nil {
		log.Errorln("Cannot initialize storage driver")
		log.Fatal(storageError)
	}

	return &storageService
}

func toDurationOrFatal(durationStr string) time.Duration {
	duration, err := time.ParseDuration(durationStr)
	if err != nil {
		log.Errorf("Cannot parse %s to duration", durationStr)
		log.Fatal(err)
	}
	return duration
}
