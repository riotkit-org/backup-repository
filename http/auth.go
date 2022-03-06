package http

import (
	"errors"
	"fmt"
	jwt "github.com/appleboy/gin-jwt/v2"
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/riotkit-org/backup-repository/users"
	"github.com/sirupsen/logrus"
	"math/rand"
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

//
// Authentication middleware is used in almost every endpoint to prevalidate user credentials
// also it provides login endpoints
//
func createAuthenticationMiddleware(r *gin.Engine, di *core.ApplicationContainer) *jwt.GinJWTMiddleware {
	authMiddleware, err := jwt.New(&jwt.GinJWTMiddleware{
		Realm:       "backup-repository",
		Key:         []byte(di.JwtSecretKey),
		Timeout:     time.Hour * 87600,
		MaxRefresh:  time.Hour * 87600,
		IdentityKey: IdentityKey,
		PayloadFunc: func(data interface{}) jwt.MapClaims {
			if v, ok := data.(*AuthUser); ok {
				claims := jwt.MapClaims{
					IdentityKey: v.UserName,
				}
				rand.Seed(time.Now().UnixNano())
				claims["rand"] = fmt.Sprintf("%v", rand.Intn(64))

				return claims
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
				return nil, jwt.ErrFailedAuthentication
			}

			if !user.IsPasswordValid(password) {
				logrus.Debugf("Invalid password for '%v'", userID)
				return nil, jwt.ErrFailedAuthentication
			}

			return &AuthUser{UserName: userID}, nil
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

			if hashedShortcut == "" {
				ServerErrorResponse(c, errors.New("too short interval between login attempts"))
				return
			}

			OKResponse(c, gin.H{
				"token":     token,
				"sessionId": hashedShortcut,
				"expire":    expire.Format(time.RFC3339),
			})
		},
	})

	if err != nil {
		logrus.Error("IsError while setting up authentication middleware")
		logrus.Fatal(err)
	}

	return authMiddleware
}

// addLookupUserRoute returns User object for a lookup
func addLookupUserRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer, rateLimiter gin.HandlerFunc) {
	r.GET("/auth/user/:userName", rateLimiter, func(c *gin.Context) {
		// subject
		user, err := ctx.Users.LookupUser(c.Param("userName"))
		if err != nil {
			NotFoundResponse(c, err)
			return
		}

		// security - check if context user has permissions to view requested user
		ctxUser, _ := GetContextUser(ctx, c)
		if !user.CanViewMyProfile(ctxUser) {
			UnauthorizedResponse(c, errors.New("no permissions to view that user account"))
			return
		}

		OKResponse(c, gin.H{
			"email":       user.Spec.Email,
			"permissions": user.Spec.Roles,
		})
	})
}

// addWhoamiRoute Returns information about current session
// sessionId is a hashed JWT, by this we identify granted accesses to be able to revoke them later
func addWhoamiRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer, rateLimiter gin.HandlerFunc) {
	r.GET("/auth/whoami", rateLimiter, func(c *gin.Context) {
		ctxUser, _ := GetContextUser(ctx, c)
		token, _ := c.Get("JWT_TOKEN")
		ga, _ := ctx.GrantedAccesses.GetGrantedAccessInformation(token.(string))

		OKResponse(c, gin.H{
			"email":         ctxUser.Spec.Email,
			"permissions":   ctxUser.Spec.Roles,
			"sessionId":     GetCurrentSessionId(c),
			"grantedAccess": ga,
		})
	})
}

// addLogoutRoute Revokes a current JWT specified in current session (e.g. from Authorization header)
// Logout does not delete GrantedAccess, but disables it so user cannot use it, but it remains in database for auditing
func addLogoutRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer, rateLimiter gin.HandlerFunc) {
	r.DELETE("/auth/logout", rateLimiter, func(c *gin.Context) {
		token, _ := c.Get("JWT_TOKEN")
		tokenFromQuery, shouldTryTokenFromQuery := c.GetQuery("sessionId")
		ctxUser, _ := GetContextUser(ctx, c)

		// permissions check: Only System Administrator can revoke other tokens
		if shouldTryTokenFromQuery {
			gaFromQuery, receiveErr := ctx.GrantedAccesses.GetGrantedAccessInformationBySessionId(tokenFromQuery)
			if receiveErr != nil {
				ServerErrorResponse(c, receiveErr)
				return
			}

			// only System Administrator can revoke tokens of other users
			// but user can revoke his/her own token
			if gaFromQuery.User != ctxUser.Metadata.Name && !ctxUser.Spec.Roles.HasRole(security.RoleSysAdmin) {
				UnauthorizedResponse(c, errors.New("you don't have permissions to revoke session of other user"))
				return
			}

			// revoke ANY session that user is granted to
			revokeErr := ctx.GrantedAccesses.RevokeSessionBySessionId(tokenFromQuery)
			if revokeErr != nil {
				NotFoundResponse(c, revokeErr)
				return
			}
		} else {
			// revoke CURRENT session user is using to perform this request
			revokeErr := ctx.GrantedAccesses.RevokeSessionByJWT(token.(string))
			if revokeErr != nil {
				ServerErrorResponse(c, revokeErr)
				return
			}
		}

		OKResponse(c, gin.H{
			"message":   "JWT was revoked",
			"sessionId": security.HashJWT(token.(string)),
		})
	})
}

// addGrantedAccessSearchRoute is useful for audit. All granted user sessions are listed there and can be revoked with a logout endpoint
func addGrantedAccessSearchRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer, rateLimiter gin.HandlerFunc) {
	r.GET("/auth/token", rateLimiter, func(c *gin.Context) {
		var userName string
		ctxUser, _ := GetContextUser(ctx, c)
		impersonateUser, shouldTryImpersonate := c.GetQuery("userName")
		userName = ctxUser.Metadata.Name

		// security: System Administrator can additionally list other user GrantedAccesses
		if shouldTryImpersonate {
			if !ctxUser.Spec.Roles.HasRole(security.RoleSysAdmin) {
				UnauthorizedResponse(c, errors.New("no permissions to act as other user"))
				return
			}

			userName = impersonateUser
		}

		tokens := ctx.GrantedAccesses.GetAllGrantedAccessesForUserByUsername(userName)

		OKResponse(c, gin.H{
			"grantedAccesses": tokens,
		})
	})
}
