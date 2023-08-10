package users

import (
	"github.com/riotkit-org/backup-repository/pkg/config"
	security2 "github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/sirupsen/logrus"
)

type Spec struct {
	Id              string                          `json:"id"`
	Email           string                          `json:"email"`
	Roles           security2.Permissions           `json:"roles"`
	Password        string                          `json:"password"`
	PasswordFromRef security2.PasswordFromSecretRef `json:"passwordFromRef"`
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
	result, err := security2.ComparePassword(password, u.getPasswordHash())
	if err != nil {
		logrus.Errorf("Cannot decode password: '%v'", err)
	}
	return result
}

//
// Security / RBAC
//

func (u User) CanViewMyProfile(actor *User) bool {
	// rbac
	if actor.Spec.Roles.HasRole(security2.RoleUserManager) {
		return true
	}

	// user can view self info
	return u.Spec.Email == actor.Spec.Email
}
