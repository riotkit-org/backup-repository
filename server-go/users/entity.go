package users

import (
	"github.com/riotkit-org/backup-repository/config"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/sirupsen/logrus"
)

type UserPermissions struct {
}

type PasswordFromSecretRef struct {
	Name  string `json:"name"`
	Entry string `json:"entry"`
}

type Spec struct {
	Id              string                `json:"id"`
	Email           string                `json:"email"`
	Permissions     UserPermissions       `json:"permissions"`
	Password        string                `json:"password"`
	PasswordFromRef PasswordFromSecretRef `json:"passwordFromRef"`
}

type User struct {
	Metadata           config.ObjectMetadata `json:"metadata"`
	Spec               Spec                  `json:"spec"`
	PasswordFromSecret string
}

func (u User) getPasswordHash() string {
	if u.PasswordFromSecret != "" {
		return u.PasswordFromSecret
	}
	return u.Spec.Password
}

func (u User) IsPasswordValid(password string) bool {
	result, err := security.ComparePassword(password, u.getPasswordHash())
	if err != nil {
		logrus.Errorf("Cannot decode password: '%v'", err)
	}
	return result
}
