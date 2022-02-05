package security

import "github.com/riotkit-org/backup-repository/config"

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

func NewGrantedAccess(jwt string, expiresAt string, active bool, description string, requesterIP string) GrantedAccess {
	// todo: reformat date

	return GrantedAccess{
		Metadata: config.ObjectMetadata{Name: HashJWT(jwt)},
		Spec: GrantedAccessSpec{
			ExpiresAt:   expiresAt,
			Active:      active,
			Description: description,
			RequesterIP: requesterIP,
		},
	}
}
