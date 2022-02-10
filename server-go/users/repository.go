package users

import (
	"encoding/json"
	"errors"
	"fmt"
	"github.com/riotkit-org/backup-repository/config"
	"github.com/sirupsen/logrus"
	"github.com/tidwall/gjson"
)

const KindBackupUser = "backupusers"

type userRepository struct {
	config.ConfigurationProvider
}

func (r userRepository) fillPasswordFromKindSecret(user *User) error {
	if user.Spec.PasswordFromRef.Name != "" {
		secretDoc, secretErr := r.GetSingleDocumentAnyType("secrets", user.Spec.PasswordFromRef.Name, "", "v1")

		if secretErr != nil {
			logrus.Errorf("Cannot fetch user hashed password from `kind: Secret`. Maybe it does not exist? %v", secretErr)
			return secretErr
		}

		secret := gjson.Get(secretDoc, fmt.Sprintf("data.%v", user.Spec.PasswordFromRef.Entry))

		if secret.String() == "" {
			logrus.Errorf(
				"Cannot retrieve password from `kind: Secret` of name '%v', field '%v'",
				user.Spec.PasswordFromRef.Name,
				user.Spec.PasswordFromRef.Entry,
			)

			return errors.New("invalid field name in `kind: Secret`")
		}

		user.PasswordFromSecret = secret.String()
		return nil
	}

	logrus.Warn("`kind: Secret` not specified for user") // todo: debug
	return nil
}

func (r userRepository) findUserByLogin(login string) (User, error) {
	doc, retrieveErr := r.GetSingleDocument(KindBackupUser, login)
	user := User{}

	if retrieveErr != nil {
		return user, errors.New(fmt.Sprintf("Error retrieving user: %v", retrieveErr))
	}

	err := json.Unmarshal([]byte(doc), &user)

	if fillErr := r.fillPasswordFromKindSecret(&user); fillErr != nil {
		return user, fillErr
	}

	return user, err
}
