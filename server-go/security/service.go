package security

import (
	"github.com/riotkit-org/backup-repository/config"
	"time"
)

type Service struct {
	repository GrantedAccessRepository
}

func (s Service) StoreJWTAsGrantedAccess(token string, expire time.Time, ip string, description string) string {
	ga := NewGrantedAccess(token, expire, true, ip, description)
	s.repository.StoreGeneratedAccessInformation(ga)

	return ga.Metadata.Name
}

func NewService(config config.ConfigurationProvider) Service {
	return Service{
		repository: GrantedAccessRepository{
			config: config,
		},
	}
}
