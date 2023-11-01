package users

import (
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/pkg/config"
	"github.com/riotkit-org/backup-repository/pkg/security"
)

type Service struct {
	repository userRepository
	config     config.ConfigurationProvider
}

// NewUsersService is a factory method
func NewUsersService(provider config.ConfigurationProvider) *Service {
	return &Service{
		repository: userRepository{provider},
		config:     provider,
	}
}

func (a *Service) LookupUser(identity security.UserIdentity) (*User, error) {
	return a.repository.findUserByLogin(identity.Username)
}

func (a *Service) LookupSessionUser(identity security.UserIdentity, scope *security.SessionLimitedOperationsScope) (*SessionAwareUser, error) {
	user, findErr := a.repository.findUserByLogin(identity.Username)
	if findErr != nil {
		return nil, errors.Wrap(findErr, "LookupSessionUser error, cannot find user")
	}

	saUser := NewSessionAwareUser(user, scope)
	if err := a.fillUpAccessToken(saUser, identity.AccessKeyName); err != nil {
		return nil, errors.Wrap(err, "LookupSessionUser error")
	}

	return saUser, nil
}

func (a *Service) fillUpAccessToken(saUser *SessionAwareUser, currentlyUsedAccessKeyName string) error {
	// access keys
	accessKeys := make([]*AccessKey, 0)
	saUser.currentAccessKey = nil
	for _, accessKey := range saUser.Spec.AccessKeys {
		ak := *accessKey
		if ak.Password == "" && ak.PasswordFromRef.Name != "" {
			hashSetter := func(password string) {
				ak.Password = password
			}
			if hashFillErr := security.FillPasswordFromKindSecret(a.config, &ak.PasswordFromRef, hashSetter); hashFillErr != nil {
				return errors.Wrap(hashFillErr, "cannot fetch access key")
			}
		}
		if accessKey.Name == currentlyUsedAccessKeyName {
			saUser.currentAccessKey = &ak
		}
		accessKeys = append(accessKeys, &ak)
	}
	saUser.accessKeysFromSecret = accessKeys
	return nil
}
