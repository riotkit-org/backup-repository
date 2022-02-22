package http

import (
	"errors"
	"fmt"
	"github.com/gin-contrib/timeout"
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/sirupsen/logrus"
	"io"
	"time"
)

func addUploadRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer, requestTimeout time.Duration) {
	timeoutMiddleware := timeout.New(
		timeout.WithTimeout(requestTimeout),
		timeout.WithHandler(func(c *gin.Context) {
			// todo: deactivate token if temporary token is used

			ctxUser, _ := GetContextUser(ctx, c)

			// Check if Collection exists
			collection, findError := ctx.Collections.GetCollectionById(c.Param("collectionId"))
			if findError != nil {
				NotFoundResponse(c, errors.New("cannot find specified collection"))
				return
			}

			// [SECURITY] Check permissions
			if !collection.CanUploadToMe(ctxUser) {
				UnauthorizedResponse(c, errors.New("not authorized to upload versions to this collection"))
			}

			// [SECURITY] Backup Windows support
			if !ctx.Collections.ValidateIsBackupWindowAllowingToUpload(collection, time.Now()) &&
				!ctxUser.Spec.Roles.HasRole(security.RoleUploadsAnytime) {

				UnauthorizedResponse(c, errors.New("backup window does not allow you to send a backup at this time. "+
					"You need a token from a user that has a special permission 'uploadsAnytime'"))
				return
			}

			// [SECURITY] Do not allow parallel uploads to the same collection
			lock, lockErr := ctx.Locks.Lock(collection.GetGlobalIdentifier(), requestTimeout)
			if lockErr != nil {
				ServerErrorResponse(c, errors.New("cannot upload to same collection in parallel"))
				return
			}
			defer lock.Unlock()

			// [ROTATION STRATEGY][VERSIONING] Increment a version, generate target file path name that will be used on storage
			sessionId := GetCurrentSessionId(c)
			version, factoryError := ctx.Storage.CreateNewVersionFromCollection(collection, ctxUser.Metadata.Name, sessionId, 0)
			if factoryError != nil {
				ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot increment version. %v", factoryError)))
				return
			}

			// [ROTATION STRATEGY] Is it allowed to upload? Is there enough space?
			rotationStrategyCase, strategyFactorialError := ctx.Storage.CreateRotationStrategyCase(collection)
			if strategyFactorialError != nil {
				logrus.Errorf(fmt.Sprintf("Cannot create collection strategy for collectionId=%v, error: %v", collection.Metadata.Name, strategyFactorialError))
				ServerErrorResponse(c, errors.New("internal error while trying to create rotation strategy. Check server logs"))
				return
			}
			if err := rotationStrategyCase.CanUpload(version); err != nil {
				UnauthorizedResponse(c, errors.New(fmt.Sprintf("backup collection strategy declined a possibility to upload, %v", err)))
				return
			}

			var stream io.ReadCloser

			// [HTTP] Support form data
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
				defer stream.Close()

			} else {
				// [HTTP] Support RAW sent data via body
				stream = c.Request.Body
			}

			// [VALIDATION] Middlewares
			versionsToDelete := rotationStrategyCase.GetVersionsThatShouldBeDeletedIfThisVersionUploaded(version)
			middlewares, err := ctx.Storage.CreateStandardMiddleWares(c.Request.Context(), versionsToDelete, collection)
			if err != nil {
				ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot construct validators %v", err)))
				return
			}

			// [HTTP] Upload a file from selected source, then handle errors - delete file from storage if not uploaded successfully
			wroteLen, uploadError := ctx.Storage.UploadFile(stream, &version, &middlewares)
			if uploadError != nil {
				_ = ctx.Storage.Delete(&version)

				ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot upload version. %v", uploadError)))
				return
			}

			// Set a valid filesize that is known after receiving the file
			version.Filesize = wroteLen

			// Append version to the registry
			if err := ctx.Storage.RegisterVersion(&version); err != nil {
				_ = ctx.Storage.Delete(&version)
			}
			ctx.Storage.CleanUpOlderVersions(versionsToDelete)
			logrus.Infof("Uploaded v%v for collectionId=%v, size=%v", version.VersionNumber, version.CollectionId, version.Filesize)

			OKResponse(c, gin.H{
				"version": version,
			})
		}),
		timeout.WithResponse(RequestTimeoutResponse),
	)

	r.POST("/repository/collection/:collectionId/version", timeoutMiddleware)
}

// todo: healthcheck route
//       to detect if anything was uploaded in previous Backup Window
//       to detect if any version is bigger than expected
