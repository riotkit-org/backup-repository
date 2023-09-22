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

func (r userRepository) findUserByLogin(identity security.UserIdentity) (*User, error) {
	doc, retrieveErr := r.GetSingleDocument(KindBackupUser, identity.Username)
	user := User{}
	if retrieveErr != nil {
		return &user, errors.New(fmt.Sprintf("IsError retrieving user: %v", retrieveErr))
	}

	if err := json.Unmarshal([]byte(doc), &user); err != nil {
		return &User{}, err
	}
	if hydrateErr := r.hydrate(&user, identity.AccessKeyName); hydrateErr != nil {
		return &User{}, hydrateErr
	}

	return &user, nil
}

func (r userRepository) hydrate(user *User, currentlyUsedAccessKeyName string) error {
	// password
	passwordSetter := func(password string) {
		user.passwordFromSecret = password
	}
	if fillErr := security.FillPasswordFromKindSecret(r, &user.Spec.PasswordFromRef, passwordSetter); fillErr != nil {
		return errors.Wrap(fillErr, "cannot fetch password")
	}

	// access keys
	accessKeys := make([]*CollectionAccessKey, 0)
	user.currentAccessKey = nil
	for _, accessKey := range user.Spec.CollectionAccessKeys {
		ak := *accessKey
		if ak.Password == "" && ak.PasswordFromRef.Name != "" {
			hashSetter := func(password string) {
				ak.Password = password
			}
			if hashFillErr := security.FillPasswordFromKindSecret(r, &ak.PasswordFromRef, hashSetter); hashFillErr != nil {
				return errors.Wrap(hashFillErr, "cannot fetch access key")
			}
		}
		if accessKey.Name == currentlyUsedAccessKeyName {
			user.currentAccessKey = &ak
		}
		accessKeys = append(accessKeys, &ak)
	}
	user.accessKeysFromSecret = accessKeys
	return nil
}
