package users

import (
	"encoding/json"
	"fmt"
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/pkg/config"
	"github.com/riotkit-org/backup-repository/pkg/security"
)

const KindBackupUser = "backupusers"

type userRepository struct {
	config.ConfigurationProvider
}

func (r userRepository) findUserByLogin(login string) (*User, error) {
	doc, retrieveErr := r.GetSingleDocument(KindBackupUser, login)
	user := User{}
	if retrieveErr != nil {
		return &user, errors.New(fmt.Sprintf("IsError retrieving user: %v", retrieveErr))
	}

	if err := json.Unmarshal([]byte(doc), &user); err != nil {
		return &User{}, err
	}
	if hydrateErr := r.hydrate(&user); hydrateErr != nil {
		return &User{}, hydrateErr
	}

	return &user, nil
}

func (r userRepository) hydrate(user *User) error {
	// password
	passwordSetter := func(password string) {
		user.passwordFromSecret = password
	}
	if fillErr := security.FillPasswordFromKindSecret(r, &user.Spec.PasswordFromRef, passwordSetter); fillErr != nil {
		return errors.Wrap(fillErr, "cannot fetch password")
	}
	return nil
}
