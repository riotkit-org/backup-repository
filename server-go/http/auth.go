package http

import (
	"errors"
	jwt "github.com/appleboy/gin-jwt/v2"
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/riotkit-org/backup-repository/users"
	"github.com/sirupsen/logrus"
	"net/http"
	"time"
)

const IdentityKey = "login"

type loginForm struct {
	Username string `form:"username" json:"username" binding:"required"`
	Password string `form:"password" json:"password" binding:"required"`
}

type AuthUser struct {
	UserName string
	subject  users.User
}

// todo: https://github.com/julianshen/gin-limiter
func createAuthenticationMiddleware(r *gin.Engine, di core.ApplicationContainer) *jwt.GinJWTMiddleware {
	authMiddleware, err := jwt.New(&jwt.GinJWTMiddleware{
		Realm:       "backup-repository",
		Key:         []byte("secret key"), // todo: PARAMETRIZE!!!
		Timeout:     time.Hour * 87600,
		MaxRefresh:  time.Hour * 87600,
		IdentityKey: IdentityKey,
		PayloadFunc: func(data interface{}) jwt.MapClaims {
			if v, ok := data.(*AuthUser); ok {
				return jwt.MapClaims{
					IdentityKey: v.UserName,
				}
			}
			return jwt.MapClaims{}
		},
		IdentityHandler: func(c *gin.Context) interface{} {
			claims := jwt.ExtractClaims(c)
			return &AuthUser{
				UserName: claims[IdentityKey].(string),
			}
		},
		Authenticator: func(c *gin.Context) (interface{}, error) {
			var loginVals loginForm
			if err := c.ShouldBind(&loginVals); err != nil {
				logrus.Warningf("Cannot bind user values in Authenticator: %v", err)
				return "", jwt.ErrMissingLoginValues
			}
			userID := loginVals.Username
			password := loginVals.Password

			user, err := di.Users.LookupUser(userID)
			logrus.Info("Looking up user", userID)

			if err != nil {
				logrus.Errorf("User lookup error: %v", err)
				return nil, errors.New("user configuration error. Please take a look at server logs")
			}

			if !user.IsPasswordValid(password) {
				logrus.Debugf("Invalid password for '%v'", userID)
				return nil, jwt.ErrFailedAuthentication
			}

			// todo: Store JWT hash shortcut to be able to revoke any JWT by admin any time

			return &AuthUser{UserName: userID, subject: user}, nil
		},
		Authorizator: func(data interface{}, c *gin.Context) bool {
			if _, ok := data.(*AuthUser); ok {
				return true
			}

			return false
		},
		Unauthorized: func(c *gin.Context, code int, message string) {
			c.JSON(code, gin.H{
				"status":  false,
				"code":    code,
				"message": message,
			})
		},
		TokenLookup:   "header: Authorization, query: token, cookie: jwt",
		TokenHeadName: "Bearer",
		TimeFunc:      time.Now,
		LoginResponse: func(c *gin.Context, code int, token string, expire time.Time) {
			c.JSON(http.StatusOK, gin.H{
				"code":   http.StatusOK,
				"token":  token,
				"expire": expire.Format(time.RFC3339),
			})

			security.StoreJWTAsGrantedAccess(token, expire, c.ClientIP(), "Login")
		},

		// todo: Check if token was not revoked
	})

	if err != nil {
		logrus.Error("Error while setting up authentication middleware")
		logrus.Fatal(err)
	}

	return authMiddleware
}

// addLookupUserRoute returns User object for a lookup
func addLookupUserRoute(r *gin.RouterGroup, ctx core.ApplicationContainer) {
	r.GET("/auth/user/:userName", func(c *gin.Context) {
		user, err := ctx.Users.LookupUser(c.Param("userName"))

		// todo: create current user context
		// todo: validate if user can lookup this user
		//user.Permissions.Can()

		if err != nil {
			c.JSON(404, gin.H{
				"status": false,
				"error":  err,
			})
			return
		}

		c.JSON(404, gin.H{
			"status": true,
			"user":   user,
		})
	})
}
