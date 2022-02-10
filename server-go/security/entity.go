package security

import (
	"gorm.io/gorm"
	"time"
)

type GrantedAccess struct {
	CreatedAt time.Time
	UpdatedAt time.Time
	DeletedAt gorm.DeletedAt `gorm:"index"`

	ID          string `json:"id"          structs:"id" sql:"type:string;primary_key;default:uuid_generate_v4()`
	ExpiresAt   string `json:"expiresAt"   structs:"expiresAt"`
	Deactivated bool   `json:"deactivated" structs:"deactivated"`
	Description string `json:"description" structs:"description"`
	RequesterIP string `json:"requesterIP" structs:"requesterIP"`
	User        string `json:"user"        structs:"user"`
}

func NewGrantedAccess(jwt string, expiresAt time.Time, deactivated bool, description string, requesterIP string, username string) GrantedAccess {
	return GrantedAccess{
		ID:          HashJWT(jwt),
		ExpiresAt:   expiresAt.Format(time.RFC3339),
		Deactivated: deactivated,
		Description: description,
		RequesterIP: requesterIP,
		User:        username,
	}
}
