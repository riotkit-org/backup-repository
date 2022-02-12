package http

import (
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
)

func SpawnHttpApplication(ctx *core.ApplicationContainer) {
	r := gin.Default()

	authMiddleware := createAuthenticationMiddleware(r, ctx)

	router := r.Group("/api/stable")
	router.POST("/auth/login", authMiddleware.LoginHandler)
	router.GET("/auth/refresh_token", authMiddleware.RefreshHandler)
	router.Use(authMiddleware.MiddlewareFunc())
	{
		addLookupUserRoute(router, ctx)
	}

	_ = r.Run()
}
