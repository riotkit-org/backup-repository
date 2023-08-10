package core

import (
	"github.com/riotkit-org/backup-repository/pkg/collections"
	"github.com/riotkit-org/backup-repository/pkg/concurrency"
	"github.com/riotkit-org/backup-repository/pkg/config"
	"github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/riotkit-org/backup-repository/pkg/storage"
	"github.com/riotkit-org/backup-repository/pkg/users"
	"gorm.io/gorm"
	"time"
)

type ApplicationContainer struct {
	Db              *gorm.DB
	Config          *config.ConfigurationProvider
	Users           *users.Service
	GrantedAccesses *security.Service
	Collections     *collections.Service
	Storage         *storage.Service
	JwtSecretKey    string
	HealthCheckKey  string
	Locks           *concurrency.LocksService

	// global timeouts
	UploadTimeout   time.Duration
	DownloadTimeout time.Duration

	// global request limit rate
	DefaultRPS          int16
	AuthRPM             int16
	CollectionHealthRPM int16
	ServerHealthRPM     int16
}
