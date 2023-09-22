package http

import (
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/pkg/core"
	"github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/riotkit-org/backup-repository/pkg/users"
)

// GetContextUser returns a User{} that is authenticated in current request
func GetContextUser(ctx *core.ApplicationContainer, c *gin.Context) (*users.User, error) {
	username, accessKeyName := security.ExtractLoginFromJWT(c.GetHeader("Authorization"))
	if accessKeyName != "" {
		return ctx.Users.LookupUser(username + "$" + accessKeyName)
	}
	return ctx.Users.LookupUser(username)
}

func GetCurrentSessionId(c *gin.Context) string {
	return security.HashJWT(c.GetHeader("Authorization")[7:])
}
