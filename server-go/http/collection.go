package http

import (
	"errors"
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
)

func addUploadRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer) {
	r.POST("/repository/collection/:collectionId/version", func(c *gin.Context) {
		// todo: check if collection exists
		// todo: check if backup window is OK
		// todo: check if rotation strategy allows uploading
		// todo: deactivate token if temporary token is used
		// todo: handle upload
		// todo: check uploaded file size, respect quotas and additional space
		// todo: check if there are gpg header and footer
		// todo: handle upload interruptions

		collection, err := ctx.Collections.GetCollectionById(c.Param("collectionId"))
		if err != nil {
			NotFoundResponse(c, errors.New("cannot find specified collection"))
			return
		}

		println(collection)
	})
}
