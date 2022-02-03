package users

import "github.com/riotkit-org/backup-repository/config"

type UserPermissions struct {
}

type User struct {
	Metadata config.ObjectMetadata `json:"metadata"`
	Spec     Spec                  `json:"spec"`
}

type Spec struct {
	Id          string          `json:"id"`
	Email       string          `json:"email"`
	Permissions UserPermissions `json:"permissions"`
}
