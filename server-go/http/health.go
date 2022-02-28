package http

import (
	"github.com/gin-gonic/gin"
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/health"
)

func addServerHealthEndpoint(r *gin.Engine, ctx *core.ApplicationContainer, rateLimiter gin.HandlerFunc) {
	r.GET("/health", rateLimiter, func(c *gin.Context) {
		// Authorization
		healthCode := c.GetHeader("Authorization")
		if healthCode == "" {
			healthCode = c.Query("code")
		}
		if healthCode != ctx.HealthCheckKey {
			UnauthorizedResponse(c, errors.New("health code invalid. Should be provided withing 'Authorization' header or 'code' query string. Must match --health-check-code commandline switch value"))
			return
		}

		healthStatuses := health.Validators{
			health.NewDbValidator(ctx.Db),
			health.NewStorageValidator(ctx.Storage),
			health.NewConfigurationProviderValidator(*ctx.Config),
		}.Validate()

		if !healthStatuses.GetOverallStatus() {
			ServerErrorResponseWithData(c, errors.New("one of checks failed"), gin.H{
				"health": healthStatuses,
			})
			return
		}

		OKResponse(c, gin.H{
			"health": healthStatuses,
		})
	})
}
