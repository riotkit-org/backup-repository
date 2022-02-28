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
	authRateLimitMiddleware := limiter.NewRateLimiter(time.Minute, 10, func(ctx *gin.Context) (string, error) {
		return "auth:" + ctx.ClientIP(), nil
	})

	// default rate limiter
	defaultRateLimitMiddleware := limiter.NewRateLimiter(time.Second, 5, func(ctx *gin.Context) (string, error) {
		return "default:" + ctx.ClientIP(), nil
	}).Middleware()

	router := r.Group("/api/stable")
	router.POST("/auth/login", authRateLimitMiddleware.Middleware(), authMiddleware.LoginHandler)
	router.GET("/auth/refresh_token", authRateLimitMiddleware.Middleware(), authMiddleware.RefreshHandler)
	router.Use(authMiddleware.MiddlewareFunc())
	{
		addLookupUserRoute(router, ctx, defaultRateLimitMiddleware)
		addWhoamiRoute(router, ctx, defaultRateLimitMiddleware)
		addLogoutRoute(router, ctx, defaultRateLimitMiddleware)
		addGrantedAccessSearchRoute(router, ctx, defaultRateLimitMiddleware)
		addUploadRoute(router, ctx, 180*time.Minute)
		addDownloadRoute(router, ctx, 180*time.Minute, defaultRateLimitMiddleware)
		addCollectionListingRoute(router, ctx, 30*time.Second, defaultRateLimitMiddleware)
	}

	// collection health
	addCollectionHealthRoute(r, ctx, limiter.NewRateLimiter(time.Minute, 10, func(ctx *gin.Context) (string, error) {
		return "collectionHealth:" + ctx.ClientIP(), nil
	}).Middleware())

	_ = r.Run()
}
