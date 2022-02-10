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
			// check if token was not revoked
			token, _ := c.Get("JWT_TOKEN")
			if !di.GrantedAccesses.IsTokenStillValid(token.(string)) {
				logrus.Debugf("Unauthorized: Token id=%v", token)
				return nil
			}

			// login user
			claims := jwt.ExtractClaims(c)
			return &AuthUser{
				UserName: claims[IdentityKey].(string),
			}
		},
		Authenticator: func(c *gin.Context) (interface{}, error) {
			var loginValues loginForm
			if err := c.ShouldBind(&loginValues); err != nil {
				logrus.Errorf("Cannot bind user values in Authenticator: %v", err)
				return "", jwt.ErrMissingLoginValues
			}

			userID := loginValues.Username
			password := loginValues.Password

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

			return &AuthUser{UserName: userID, subject: user}, nil
		},
		Authorizator: func(data interface{}, c *gin.Context) bool {
			if _, ok := data.(*AuthUser); ok {
				return true
			}

			return false
		},
		Unauthorized: func(c *gin.Context, code int, message string) {
			c.IndentedJSON(code, gin.H{
				"status":  false,
				"code":    code,
				"message": message,
				"data":    gin.H{},
			})
		},
		TokenLookup:   "header: Authorization, query: token, cookie: jwt",
		TokenHeadName: "Bearer",
		TimeFunc:      time.Now,
		LoginResponse: func(c *gin.Context, code int, token string, expire time.Time) {
			hashedShortcut := di.GrantedAccesses.StoreJWTAsGrantedAccess(
				token, expire, c.ClientIP(), "Login", security.ExtractLoginFromJWT(token))

			c.IndentedJSON(http.StatusOK, gin.H{
				"code": http.StatusOK,
				"data": gin.H{
					"token":  token,
					"hash":   hashedShortcut,
					"expire": expire.Format(time.RFC3339),
				},
			})
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
			c.IndentedJSON(404, gin.H{
				"status": false,
				"error":  err,
				"data":   gin.H{},
			})
			return
		}

		c.IndentedJSON(200, gin.H{
			"status": true,
			"data": gin.H{
				"email":       user.Spec.Email,
				"permissions": user.Spec.Permissions,
			},
		})
	})
}
