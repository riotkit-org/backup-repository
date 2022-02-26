package security

import (
	"github.com/sirupsen/logrus"
	"gorm.io/gorm"
	"time"
)

//
// User permissions
//

type Permissions []string

func (p Permissions) HasRole(name string) bool {
	return p.has(name) || p.has(RoleSysAdmin)
}

func (p Permissions) has(name string) bool {
	for _, cursor := range p {
		if cursor == name {
			return true
		}
	}

	return false
}

//
// Permissions for objects
//

type AccessControlObject struct {
	UserName string      `json:"userName"`
	Roles    Permissions `json:"roles"`
}

type AccessControlList []AccessControlObject

// IsPermitted checks if given user is granted a role in this list
func (acl AccessControlList) IsPermitted(username string, role string) bool {
	for _, permitted := range acl {
		if permitted.UserName == username && permitted.Roles.HasRole(role) {
			return true
		}
	}
	return false
}

//
// PasswordFromSecretRef references passwords stored in ConfigMaps
//    Name is the ConfigMap name
//    Entry is the key name in .data
//
type PasswordFromSecretRef struct {
	Name  string `json:"name"`
	Entry string `json:"entry"`
}

//
// GrantedAccess stores information about generated JWT tokens (successful logins to the system)
//
type GrantedAccess struct {
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt `gorm:"index"`

	ID          string    `json:"id"          structs:"id" sql:"type:string;primary_key;default:uuid_generate_v4()`
	ExpiresAt   time.Time `json:"expiresAt"   structs:"expiresAt"`
	Deactivated bool      `json:"deactivated" structs:"deactivated"`
	Description string    `json:"description" structs:"description"`
	RequesterIP string    `json:"requesterIP" structs:"requesterIP"`
	User        string    `json:"user"        structs:"user"`
}

func (ga GrantedAccess) IsNotExpired() bool {
	return time.Now().After(ga.ExpiresAt)
}

func (ga GrantedAccess) IsValid() bool {
	if ga.Deactivated {
		logrus.Warningf("IsValid(false): Account is deactivated [id=%v]", ga.ID)
		return false
	}

	if ga.DeletedAt.Valid {
		logrus.Warningf("IsValid(false): JWT deleted [id=%v]", ga.ID)
		return false
	}

	if ga.IsNotExpired() {
		logrus.Warningf("IsValid(false): JWT expired [id=%v]", ga.ID)
		return false
	}

	return true
}

func NewGrantedAccess(jwt string, expiresAt time.Time, deactivated bool, description string, requesterIP string, username string) GrantedAccess {
	return GrantedAccess{
		ID:          HashJWT(jwt),
		ExpiresAt:   expiresAt,
		Deactivated: deactivated,
		Description: description,
		RequesterIP: requesterIP,
		User:        username,
	}
}
