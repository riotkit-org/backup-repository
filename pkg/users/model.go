package users

import (
	"github.com/riotkit-org/backup-repository/pkg/config"
	"github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/sirupsen/logrus"
	"k8s.io/utils/strings/slices"
)

type CollectionAccessKey struct {
	Name            string                         `json:"name"`
	Password        string                         `json:"password"` // master password to this account, allows access limited by
	PasswordFromRef security.PasswordFromSecretRef `json:"passwordFromRef"`
	Collections     []string                       `json:"collections"`
	Roles           security.Permissions           `json:"roles"` // roles allowed in context of all listed collections. Limited by User global roles and Collection roles
}

type Spec struct {
	Id                   string                         `json:"id"`
	Email                string                         `json:"email"`
	Roles                security.Permissions           `json:"roles"`
	Password             string                         `json:"password"` // master password to this account, allows access limited by
	PasswordFromRef      security.PasswordFromSecretRef `json:"passwordFromRef"`
	CollectionAccessKeys []*CollectionAccessKey         `json:"collectionAccessKeys"`
}

type User struct {
	Metadata config.ObjectMetadata `json:"metadata"`
	Spec     Spec                  `json:"spec"`

	// Dynamic property: User's password hash
	passwordFromSecret string

	// Dynamic property: Copy of .spec.CollectionAccessKeys with password field filled up
	accessKeysFromSecret []*CollectionAccessKey

	// Dynamic property: Access key used in current session
	currentAccessKey *CollectionAccessKey
}

func (u User) GetRoles() security.Permissions {
	return u.Spec.Roles
}

// IsInAccessKeyContext returns true, when User is authenticated using CollectionAccessKey in current context
func (u User) IsInAccessKeyContext() bool {
	return u.currentAccessKey != nil
}

// GetAccessKeyRolesInCollectionContext is returning User permissions in context of a Collection, limited by CollectionAccessKey roles
func (u User) GetAccessKeyRolesInCollectionContext(collectionId string) security.Permissions {
	if u.currentAccessKey != nil && slices.Contains(u.currentAccessKey.Collections, collectionId) {
		return u.currentAccessKey.Roles
	}
	return security.Permissions{}
}

// getPasswordHash is returning a hashed password (Argon2 hash for comparison)
func (u User) getPasswordHash() string {
	if u.passwordFromSecret != "" {
		return u.passwordFromSecret
	}
	return u.Spec.Password
}

// IsPasswordValid is checking if User supplied password matches User's main password,
// or CollectionAccessKey password - depending on accessKeyName parameter
func (u User) IsPasswordValid(password string, accessKeyName string) bool {
	if accessKeyName != "" {
		for _, accessKey := range u.accessKeysFromSecret {
			if accessKey.Name == accessKeyName {
				result, err := security.ComparePassword(password, accessKey.Password)
				if err != nil {
					logrus.Errorf("Cannot decode access key: '%v'", err)
				}
				return result
			}
		}
		logrus.Warnf("Invalid access key '%s' requested for user '%s'", accessKeyName, u.Metadata.Name)
		return false
	}

	result, err := security.ComparePassword(password, u.getPasswordHash())
	if err != nil {
		logrus.Errorf("Cannot decode password: '%v'", err)
	}
	return result
}

//
// Security / RBAC
//

// CanViewMyProfile RBAC method
func (u User) CanViewMyProfile(actor *User) bool {
	// rbac
	if actor.GetRoles().HasRole(security.RoleUserManager) {
		return true
	}

	// user can view self info
	return u.Spec.Email == actor.Spec.Email
}
