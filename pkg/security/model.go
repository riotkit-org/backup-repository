package security

import (
	"github.com/sirupsen/logrus"
	"gorm.io/gorm"
	"strings"
	"time"
)

type ScopedElement struct {
	Type  string   `form:"type" json:"type" binding:"required"`
	Name  string   `form:"name" json:"name" binding:"required"`
	Roles []string `form:"roles" json:"roles" binding:"required"`
}

// SessionLimitedOperationsScope allows to define additional limitations on the user's JWT token, so even if user has higher permissions we can limit those permissions per JWT token
type SessionLimitedOperationsScope struct {
	Elements []ScopedElement `form:"elements" json:"elements"`
}

//
// User permissions - roles
//

type Roles []string

func (p Roles) IsEmpty() bool {
	return len(p) == 0
}

func (p Roles) HasRole(name string) bool {
	return p.has(name) || p.has(RoleSysAdmin)
}

func (p Roles) has(name string) bool {
	for _, cursor := range p {
		if cursor == name {
			return true
		}
	}

	return false
}

//
// Roles for objects
//

type AccessControlObject struct {
	Name  string `json:"name"`
	Type  string `json:"type"`
	Roles Roles  `json:"roles"`
}

type AccessControlList []AccessControlObject

// IsPermitted checks if given user is granted a role in this list
func (acl AccessControlList) IsPermitted(name string, objType string, action string) bool {
	for _, permitted := range acl {
		if permitted.Name == name && permitted.Type == objType && CanThoseRolesPerformAction(permitted.Roles, action) {
			return true
		}
	}
	return false
}

// PasswordFromSecretRef references passwords stored in ConfigMaps
//
//	Name is the ConfigMap name
//	Entry is the key name in .data
type PasswordFromSecretRef struct {
	Name  string `json:"name"`
	Entry string `json:"entry"`
}

// GrantedAccess stores information about generated JWT tokens (successful logins to the system)
type GrantedAccess struct {
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt `gorm:"index"`

	ID            string    `json:"id"            structs:"id" sql:"type:string;primary_key;default:uuid_generate_v4()"`
	ExpiresAt     time.Time `json:"expiresAt"     structs:"expiresAt"`
	Deactivated   bool      `json:"deactivated"   structs:"deactivated"`
	Description   string    `json:"description"   structs:"description"`
	RequesterIP   string    `json:"requesterIP"   structs:"requesterIP"`
	User          string    `json:"user"          structs:"user"`
	AccessKeyName string    `json:"accessKeyName" structs:"accessKeyName"`
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

func NewGrantedAccess(jwt string, expiresAt time.Time, deactivated bool, description string, requesterIP string, username string, accessKeyName string) GrantedAccess {
	return GrantedAccess{
		ID:            HashJWT(jwt),
		ExpiresAt:     expiresAt,
		Deactivated:   deactivated,
		Description:   description,
		RequesterIP:   requesterIP,
		User:          username,
		AccessKeyName: accessKeyName,
	}
}

type UserIdentity struct {
	Username      string
	AccessKeyName string
}

func NewUserIdentityFromString(login string) UserIdentity {
	if strings.Contains(login, "$") {
		lastIndex := strings.LastIndex(login, "$")
		return UserIdentity{
			Username:      login[:lastIndex],
			AccessKeyName: login[lastIndex+1:],
		}
	}
	return UserIdentity{
		Username:      login,
		AccessKeyName: "",
	}
}
