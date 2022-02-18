package http

import (
	"errors"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/sirupsen/logrus"
	"io"
	"time"
)

func addUploadRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer) {
	r.POST("/repository/collection/:collectionId/version", func(c *gin.Context) {
		// todo: check if rotation strategy allows uploading
		// todo: deactivate token if temporary token is used
		// todo: check uploaded file size, respect quotas and additional space
		// todo: handle upload interruptions
		// todo: locking support! There should be no concurrent uploads to the same collection

		ctxUser, _ := GetContextUser(ctx, c)

		// Check if Collection exists
		collection, findError := ctx.Collections.GetCollectionById(c.Param("collectionId"))
		if findError != nil {
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

		// Increment a version, generate target file path name that will be used on storage
		sessionId := GetCurrentSessionId(c)
		version, factoryError := ctx.Storage.CreateNewVersionFromCollection(collection, ctxUser.Metadata.Name, sessionId, 0)
		if factoryError != nil {
			ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot increment version. %v", factoryError)))
			return
		}

		// Check rotation strategy: Is it allowed to upload? Is there enough space?
		rotationStrategyCase, strategyFactorialError := ctx.Storage.CreateRotationStrategyCase(collection)
		if strategyFactorialError != nil {
			logrus.Errorf(fmt.Sprintf("Cannot create collection strategy for collectionId=%v, error: %v", collection.Metadata.Name, strategyFactorialError))
			ServerErrorResponse(c, errors.New("internal error while trying to create rotation strategy. Check server logs"))
		}
		if err := rotationStrategyCase.CanUpload(version); err != nil {
			UnauthorizedResponse(c, errors.New(fmt.Sprintf("backup collection strategy declined a possibility to upload, %v", err)))
			return
		}

		var stream io.ReadCloser

		// Support form data
		if c.ContentType() == "application/x-www-form-urlencoded" || c.ContentType() == "multipart/form-data" {
			var openErr error
			fh, ffErr := c.FormFile("file")
			if ffErr != nil {
				ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot read file from multipart/urlencoded form: %v", ffErr)))
				return
			}
			stream, openErr = fh.Open()
			if openErr != nil {
				ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot open file from multipart/urlencoded form: %v", openErr)))
			}

		} else {
			// Support RAW sent data via body
			stream = c.Request.Body
		}

		// Upload a file from selected source, then handle errors - delete file from storage if not uploaded successfully
		wroteLen, uploadError := ctx.Storage.UploadFile(stream, &version)
		if uploadError != nil {
			// todo: make sure the uploaded file will be deleted

			ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot upload version. %v", uploadError)))
			return
		}

		// Set a valid filesize that is known after receiving the file
		version.Filesize = wroteLen

		// Append version to the registry
		// todo
		//ctx.Storage.SubmitVersion(version)
		//ctx.Storage.CleanUpOlderVersions(rotationStrategyCase.GetVersionsThatShouldBeDeletedIfThisVersionUploaded(version))

		// todo: Rotate collection
		// todo: add UploadedVersion to database
		OKResponse(c, gin.H{
			"version": version,
		})
	})
}
