package http

import (
	"errors"
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/security"
	"time"
)

func addUploadRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer) {
	r.POST("/repository/collection/:collectionId/version", func(c *gin.Context) {
		// todo: check if rotation strategy allows uploading
		// todo: deactivate token if temporary token is used
		// todo: handle upload
		// todo: check uploaded file size, respect quotas and additional space
		// todo: check if there are gpg header and footer
		// todo: handle upload interruptions

		ctxUser, _ := GetContextUser(ctx, c)

		// Check if Colection exists
		collection, err := ctx.Collections.GetCollectionById(c.Param("collectionId"))
		if err != nil {
			NotFoundResponse(c, errors.New("cannot find specified collection"))
			return
		}

		// Check permissions
		if !collection.CanUploadToMe(ctxUser) {
			UnauthorizedResponse(c, errors.New("not authorized to upload versions to this collection"))
		}

		// Backup Windows support
		if !ctx.Collections.ValidateIsBackupWindowAllowingToUpload(collection, time.Now()) &&
			!ctxUser.Spec.Roles.HasRole(security.RoleUploadsAnytime) {

			UnauthorizedResponse(c, errors.New("backup window does not allow you to send a backup at this time. "+
				"You need a token from a user that has a special permission 'uploadsAnytime'"))
			return
		}

		// todo: support Url encoded and raw body
		// c.Request.Body
		// ctx.Storage

		println(collection)
	})
}
