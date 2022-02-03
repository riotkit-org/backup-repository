package users

import (
	"encoding/json"
	"errors"
	"fmt"
	"github.com/riotkit-org/backup-repository/config"
)

const KIND = "BackupUser"

type userRepository struct {
	config.ConfigurationProvider
}

func (r userRepository) findUserByLogin(login string) (User, error) {
	doc, retrieveErr := r.GetSingleDocument(KIND, login)
	user := User{}

	if retrieveErr != nil {
		return user, errors.New(fmt.Sprintf("Error retrieving user: %v", retrieveErr))
	}

	err := json.Unmarshal([]byte(doc), &user)

	return user, err
}
