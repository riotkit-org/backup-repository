package http

import (
	"context"
	"github.com/gin-gonic/gin"
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/pkg/core"
	health2 "github.com/riotkit-org/backup-repository/pkg/health"
	"github.com/sirupsen/logrus"
)

func addServerHealthEndpoints(r *gin.Engine, ctx *core.ApplicationContainer, rateLimiter gin.HandlerFunc) {
	// responds if service is running (there is enough ram memory, HTTP request can be processed etc.)
	r.GET("/health", rateLimiter, func(c *gin.Context) {
		OKResponse(c, gin.H{
			"msg": "The server is up and running. Dependent services are not shown there. Take a look at /ready endpoint",
		})
	})

	// responds if service can handle requests actually
	r.GET("/ready", rateLimiter, func(c *gin.Context) {
		// Authorization
		healthCode := c.GetHeader("Authorization")
		if healthCode == "" {
			healthCode = c.Query("code")
		}
		if healthCode != ctx.HealthCheckKey {
			UnauthorizedResponse(c, errors.New("health code invalid. Should be provided withing 'Authorization' header or 'code' query string. Must match --health-check-code commandline switch value"))
			return
		}

		healthStatuses := health2.Validators{
			health2.NewDbValidator(ctx.Db),
			health2.NewStorageValidator(ctx.Storage, context.Background(), ctx.Storage.HealthTimeout),
			health2.NewConfigurationProviderValidator(*ctx.Config),
		}.Validate()

		if !healthStatuses.GetOverallStatus() {
			logrus.Errorf("The server is unhealthy: %v", healthStatuses)

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
