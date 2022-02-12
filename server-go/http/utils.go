package http

import (
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/riotkit-org/backup-repository/users"
)

// GetContextUser returns a User{} that is authenticated in current request
func GetContextUser(ctx *core.ApplicationContainer, c *gin.Context) (*users.User, error) {
	username := security.ExtractLoginFromJWT(c.GetHeader("Authorization"))

	return ctx.Users.LookupUser(username)
}

func GetCurrentSessionId(c *gin.Context) string {
	return security.HashJWT(c.GetHeader("Authorization")[7:])
}
