package core

import (
	"github.com/riotkit-org/backup-repository/config"
	"github.com/riotkit-org/backup-repository/users"
)

type ApplicationContainer struct {
	Config config.ConfigurationProvider
	Users  users.Service
}
