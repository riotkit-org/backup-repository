package users

import "github.com/riotkit-org/backup-repository/config"

type Service struct {
	userRepository
}

// NewUsersService is a factory method
func NewUsersService(provider config.ConfigurationProvider) Service {
	return Service{
		userRepository{provider},
	}
}

func (a Service) LookupUser(login string) (User, error) {
	return a.findUserByLogin(login)
}
