package users

import (
	"github.com/riotkit-org/backup-repository/pkg/config"
	"github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/sirupsen/logrus"
)

type CollectionAccessKey struct {
	Name            string                         `json:"name"`
	Password        string                         `json:"password"` // master password to this account, allows access limited by
	PasswordFromRef security.PasswordFromSecretRef `json:"passwordFromRef"`
	Objects         []security.AccessControlObject `json:"objects"`
}

type Spec struct {
	Id                   string                         `json:"id"`
	Email                string                         `json:"email"`
	Roles                security.Roles                 `json:"roles"`
	Password             string                         `json:"password"` // master password to this account, allows access limited by
	PasswordFromRef      security.PasswordFromSecretRef `json:"passwordFromRef"`
	CollectionAccessKeys []*CollectionAccessKey         `json:"collectionAccessKeys"`
}

type User struct {
	Metadata config.ObjectMetadata `json:"metadata"`
	Spec     Spec                  `json:"spec"`

	// Dynamic property: User's password hash
	passwordFromSecret string
}

func (u User) GetRoles() security.Roles {
	return u.Spec.Roles
}

// getPasswordHash is returning a hashed password (Argon2 hash for comparison)
func (u User) getPasswordHash() string {
	if u.passwordFromSecret != "" {
		return u.passwordFromSecret
	}
	return u.Spec.Password
}

// IsPasswordValid is checking if User supplied password matches User's main password
func (u User) isPasswordValid(password string) bool {
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
func (u User) CanViewMyProfile(actor security.Actor) bool {
	// rbac
	if actor.GetRoles().HasRole(security.RoleUserManager) {
		return true
	}

	// user can view self info
	return u.Spec.Email == actor.GetEmail()
}

func NewSessionAwareUser(u *User, scope *security.SessionLimitedOperationsScope) *SessionAwareUser {
	return &SessionAwareUser{
		User:         u,
		sessionScope: scope,
	}
}

type SessionAwareUser struct {
	*User

	// Dynamic property: Copy of .spec.CollectionAccessKeys with password field filled up
	accessKeysFromSecret []*CollectionAccessKey

	// Dynamic property: Access key used in current session
	currentAccessKey *CollectionAccessKey

	// Dynamic property: Read from JWT token - operations scope, limited per session/token
	sessionScope *security.SessionLimitedOperationsScope
}

func (sau *SessionAwareUser) GetSessionLimitedOperationsScope() *security.SessionLimitedOperationsScope {
	return sau.sessionScope
}

func (sau *SessionAwareUser) GetEmail() string {
	return sau.Spec.Email
}

func (sau *SessionAwareUser) GetTypeName() string {
	return "user"
}

func (sau *SessionAwareUser) IsInAccessKeyContext() bool {
	return sau.currentAccessKey != nil
}

func (sau *SessionAwareUser) GetAccessKeyRolesInContextOf(subject security.Subject) security.Roles {
	if sau.currentAccessKey != nil {
		for _, object := range sau.currentAccessKey.Objects {
			if object.Type == subject.GetTypeName() && object.Name == subject.GetId() {
				return object.Roles
			}
		}
	}
	return security.Roles{}
}

// IsPasswordValid is checking if User supplied password matches User's main password,
// or CollectionAccessKey password - depending on accessKeyName parameter
func (sau *SessionAwareUser) IsPasswordValid(password string, accessKeyName string) bool {
	if accessKeyName != "" {
		for _, accessKey := range sau.accessKeysFromSecret {
			if accessKey.Name == accessKeyName {
				result, err := security.ComparePassword(password, accessKey.Password)
				if err != nil {
					logrus.Errorf("Cannot decode access key: '%v'", err)
				}
				return result
			}
		}
		logrus.Warnf("Invalid access key '%s' requested for user '%s'", accessKeyName, sau.Metadata.Name)
		return false
	}

	return sau.isPasswordValid(password)
}

func (sau *SessionAwareUser) GetRoles() security.Roles {
	return sau.User.GetRoles()
}

func (sau *SessionAwareUser) GetName() string {
	return sau.User.Metadata.Name
}
