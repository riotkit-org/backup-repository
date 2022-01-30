package http

import (
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/users"
)

// addLookupUserRoute returns User object for a lookup
func addLookupUserRoute(r *gin.Engine) {
	r.GET("/api/stable/auth/user/:userId", func(c *gin.Context) {

		// todo: create current user context
		// todo: validate if user can lookup this user

		user, err := users.LookupUser(c.Param("userId"))

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
