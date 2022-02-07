package security

import (
	"github.com/riotkit-org/backup-repository/config"
	"time"
)

type GrantedAccessSpec struct {
	ExpiresAt   string `json:"expiresAt"`
	Active      bool   `json:"active"`
	Description string `json:"description"`
	RequesterIP string `json:"requesterIP"`
}

type GrantedAccess struct {
	Metadata config.ObjectMetadata `json:"metadata"`
	Spec     GrantedAccessSpec     `json:"grantedAccessSpec"`
}

func NewGrantedAccess(jwt string, expiresAt time.Time, active bool, description string, requesterIP string) GrantedAccess {
	return GrantedAccess{
		Metadata: config.ObjectMetadata{Name: HashJWT(jwt)},
		Spec: GrantedAccessSpec{
			ExpiresAt:   expiresAt.Format(time.RFC3339),
			Active:      active,
			Description: description,
			RequesterIP: requesterIP,
		},
	}
}
