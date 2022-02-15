package core

import (
	"github.com/riotkit-org/backup-repository/collections"
	"github.com/riotkit-org/backup-repository/config"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/riotkit-org/backup-repository/storage"
	"github.com/riotkit-org/backup-repository/users"
)

type ApplicationContainer struct {
	Config          *config.ConfigurationProvider
	Users           *users.Service
	GrantedAccesses *security.Service
	Collections     *collections.Service
	Storage         *storage.Service
	JwtSecretKey    string
}
