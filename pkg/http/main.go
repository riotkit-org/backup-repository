package http

import (
	"github.com/gin-gonic/gin"
	limiter "github.com/julianshen/gin-limiter"
	"github.com/riotkit-org/backup-repository/pkg/core"
	"time"
)

func SpawnHttpApplication(app *core.ApplicationContainer, listenAddr string) error {
	r := gin.Default()

	authMiddleware := createAuthenticationMiddleware(r, app)
	errorLoggerMiddleware := responseErrorLoggerMiddleware()

	// set a rate limit of 10 requests per minute for IP address to protect against DoS attacks on login and refresh_token endpoints
	// for two reasons:
	//     1) to protect against brute force
	//     2) to protect against memory overflow attacks (argon2di uses a lot of memory to calculate hash during login. But that's intended - the password is a lot more difficult to crack in case, when hash would leak)
	authRateLimitMiddleware := limiter.NewRateLimiter(time.Minute, int64(app.AuthRPM), func(ctx *gin.Context) (string, error) {
		return "auth:" + ctx.ClientIP(), nil
	})

	// default rate limiter
	defaultRateLimitMiddleware := limiter.NewRateLimiter(time.Second, int64(app.DefaultRPS), func(ctx *gin.Context) (string, error) {
		return "default:" + ctx.ClientIP(), nil
	}).Middleware()

	router := r.Group("/")
	router.POST("/api/stable/auth/login", errorLoggerMiddleware, authRateLimitMiddleware.Middleware(), authMiddleware.LoginHandler)
	router.GET("/api/stable/auth/refresh_token", errorLoggerMiddleware, authRateLimitMiddleware.Middleware(), authMiddleware.RefreshHandler)
	router.Use(authMiddleware.MiddlewareFunc(), errorLoggerMiddleware)
	{
		addLookupUserRoute(router, app, defaultRateLimitMiddleware)
		addWhoamiRoute(router, app, defaultRateLimitMiddleware)
		addLogoutRoute(router, app, defaultRateLimitMiddleware)
		addGrantedAccessSearchRoute(router, app, defaultRateLimitMiddleware)
		addUploadRoute(router, app, app.UploadTimeout)
		addDownloadRoute(router, app, app.DownloadTimeout, defaultRateLimitMiddleware)
		addCollectionListingRoute(router, app, 30*time.Second, defaultRateLimitMiddleware)
	}

	// collection health
	addCollectionHealthRoute(r, app, limiter.NewRateLimiter(time.Minute, int64(app.CollectionHealthRPM), func(ctx *gin.Context) (string, error) {
		return "collectionHealth:" + ctx.ClientIP(), nil
	}).Middleware())

	// server health
	addServerHealthEndpoints(r, app, limiter.NewRateLimiter(time.Minute, int64(app.ServerHealthRPM), func(ctx *gin.Context) (string, error) {
		return "health:" + ctx.ClientIP(), nil
	}).Middleware())

	return r.Run(listenAddr)
}
