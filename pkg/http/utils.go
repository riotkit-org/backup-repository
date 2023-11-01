package http

import (
	"errors"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/pkg/core"
	"github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/riotkit-org/backup-repository/pkg/users"
	"strings"
)

// GetContextUser returns a User{} that is authenticated in current request
func GetContextUser(ctx *core.ApplicationContainer, c *gin.Context) (*users.SessionAwareUser, error) {
	username, accessKeyName := security.ExtractLoginFromJWT(c.GetHeader("Authorization"))
	scope, scopeErr := security.ExtractSessionLimitedOperationsScopeFromJWT(c.GetHeader("Authorization"))
	if scopeErr != nil {
		return nil, errors.New(fmt.Sprintf("cannot create context user: %s", scopeErr.Error()))
	}

	identity := security.NewUserIdentityFromString(username)
	identity.AccessKeyName = accessKeyName

	if accessKeyName != "" {
		return ctx.Users.LookupSessionUser(identity, scope)
	}
	return ctx.Users.LookupSessionUser(identity, scope)
}

func GetCurrentSessionId(c *gin.Context) string {
	return security.HashJWT(strings.Trim(c.GetHeader("Authorization"), " ")[7:])
}
