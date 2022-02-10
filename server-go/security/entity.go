package security

import (
	"github.com/sirupsen/logrus"
	"gorm.io/gorm"
	"time"
)

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
		logrus.Error("IsValid(false): Account is deactivated")
		return false
	}

	if ga.DeletedAt.Valid {
		logrus.Error("IsValid(false): JWT deleted")
		return false
	}

	if ga.IsNotExpired() {
		logrus.Error("IsValid(false): JWT expired")
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
