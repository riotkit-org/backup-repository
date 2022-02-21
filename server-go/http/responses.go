package http

import (
	"github.com/gin-gonic/gin"
	"net/http"
)

func NotFoundResponse(c *gin.Context, err error) {
	c.IndentedJSON(404, gin.H{
		"status": false,
		"error":  err.Error(),
		"data":   gin.H{},
	})
}

func OKResponse(c *gin.Context, data gin.H) {
	c.IndentedJSON(200, gin.H{
		"status": true,
		"data":   data,
	})
}

func UnauthorizedResponse(c *gin.Context, err error) {
	c.IndentedJSON(403, gin.H{
		"status": false,
		"error":  err.Error(),
		"data":   gin.H{},
	})
}

func ServerErrorResponse(c *gin.Context, err error) {
	c.IndentedJSON(500, gin.H{
		"status": false,
		"error":  err.Error(),
		"data":   gin.H{},
	})
}

func RequestTimeoutResponse(c *gin.Context) {
	c.IndentedJSON(http.StatusRequestTimeout, gin.H{
		"status": false,
		"error":  "Request took too long",
		"data":   gin.H{},
	})
}
