package http

import (
	"github.com/gin-gonic/gin"
	limiter "github.com/julianshen/gin-limiter"
	"github.com/riotkit-org/backup-repository/core"
	"time"
)

func SpawnHttpApplication(ctx *core.ApplicationContainer) {
	r := gin.Default()

	authMiddleware := createAuthenticationMiddleware(r, ctx)

	// set a rate limit of 10 requests per minute for IP address to protect against DoS attacks on login and refresh_token endpoints
	// for two reasons:
	//     1) to protect against brute force
	//     2) to protect against memory overflow attacks (argon2di uses a lot of memory to calculate hash during login. But that's intended - the password is a lot more difficult to crack in case, when hash would leak)
	rateLimitMiddleware := limiter.NewRateLimiter(time.Minute, 10, func(ctx *gin.Context) (string, error) {
		return ctx.ClientIP(), nil
	})

	router := r.Group("/api/stable")
	router.POST("/auth/login", rateLimitMiddleware.Middleware(), authMiddleware.LoginHandler)
	router.GET("/auth/refresh_token", rateLimitMiddleware.Middleware(), authMiddleware.RefreshHandler)
	router.Use(authMiddleware.MiddlewareFunc())
	{
		addLookupUserRoute(router, ctx)
		addWhoamiRoute(router, ctx)
		addLogoutRoute(router, ctx)
		addGrantedAccessSearchRoute(router, ctx)
		addUploadRoute(router, ctx, 180*time.Minute)
	}

	_ = r.Run()
}
