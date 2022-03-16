package http

import (
	"context"
	"errors"
	"fmt"
	"github.com/gin-contrib/timeout"
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
	"github.com/riotkit-org/backup-repository/health"
	"github.com/riotkit-org/backup-repository/security"
	"github.com/riotkit-org/backup-repository/storage"
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
				return
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
			wroteLen, uploadError := ctx.Storage.UploadFile(c.Request.Context(), stream, &version, &middlewares)
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

// addDownloadRoute adds a collection version download endpoint
func addDownloadRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer, requestTimeout time.Duration, rateLimiter gin.HandlerFunc) {
	timeoutMiddleware := timeout.New(
		timeout.WithTimeout(requestTimeout),
		timeout.WithHandler(func(c *gin.Context) {
			ctxUser, _ := GetContextUser(ctx, c)

			// Check if Collection exists
			collection, findError := ctx.Collections.GetCollectionById(c.Param("collectionId"))
			if findError != nil {
				NotFoundResponse(c, errors.New("cannot find specified collection"))
				return
			}

			// [SECURITY] Check permissions
			if !collection.CanDownloadFromMe(ctxUser) {
				UnauthorizedResponse(c, errors.New("not authorized to download versions from this collection"))
				return
			}

			// Check version
			version, err := ctx.Storage.GetVersionByNum(collection.GetId(), c.Param("versionNum"))
			if err != nil {
				NotFoundResponse(c, errors.New("cannot find specified version"))
				return
			}

			// Read from storage
			middlewares := storage.NestedStreamMiddlewares{}
			stream, err := ctx.Storage.ReadFile(c.Request.Context(), version.GetTargetPath())
			if err != nil {
				ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot read from storage: %v", err)))
				return
			}

			// Inform the browser about content type
			c.Header("Content-Type", "application/octet-stream")
			c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=\"%v\"", version.Filename))
			c.Header("Content-Length", fmt.Sprintf("%v", version.Filesize))

			// Write output directly to the HTTP response writer
			if _, err := ctx.Storage.CopyStream(context.Background(), stream, c.Writer, 1024*1024, &middlewares); err != nil {
				ServerErrorResponse(c, errors.New(fmt.Sprintf("cannot copy stream to download a version: %v", err)))
				return
			}
		}),
		timeout.WithResponse(RequestTimeoutResponse),
	)
	r.GET("/repository/collection/:collectionId/version/:versionNum", rateLimiter, timeoutMiddleware)
}

// addCollectionHealthRoute is creating an anonymous-access route that exposes health check
func addCollectionHealthRoute(r *gin.Engine, ctx *core.ApplicationContainer, rateLimiter gin.HandlerFunc) {
	r.GET("/api/stable/repository/collection/:collectionId/health", rateLimiter, func(c *gin.Context) {
		// Check if Collection exists
		collection, findError := ctx.Collections.GetCollectionById(c.Param("collectionId"))
		if findError != nil {
			logrus.Errorln(findError)
			NotFoundResponse(c, errors.New("cannot find specified collection"))
			return
		}

		// OPTIONAL: If collection has a healthcheck secret, then verify it. Secret is an any phrase, stored as sha256 hash
		authCode := c.GetHeader("Authorization")
		if c.Query("code") != "" {
			authCode = c.Query("code")
		}
		if !collection.IsHealthCheckSecretValid(authCode) {
			UnauthorizedResponse(c, errors.New("not authorized - invalid value of 'code' parameter in query string"))
			return
		}

		// Run all the checks
		healthStatuses := health.Validators{
			health.NewBackupWindowValidator(ctx.Storage, collection),
			health.NewVersionsSizeValidator(ctx.Storage, collection),
			health.NewSumOfVersionsValidator(ctx.Storage, collection),
		}.Validate()

		if !healthStatuses.GetOverallStatus() {
			ServerErrorResponseWithData(c, errors.New("one of checks failed"), gin.H{
				"health": healthStatuses,
			})
			return
		}

		OKResponse(c, gin.H{
			"health": healthStatuses,
		})
	})
}

func addCollectionListingRoute(r *gin.RouterGroup, ctx *core.ApplicationContainer, requestTimeout time.Duration, rateLimiter gin.HandlerFunc) {
	r.GET("/repository/collection/:collectionId/version", rateLimiter, func(c *gin.Context) {
		ctxUser, _ := GetContextUser(ctx, c)

		// Check if Collection exists
		collection, findError := ctx.Collections.GetCollectionById(c.Param("collectionId"))
		if findError != nil {
			logrus.Errorln(findError)
			NotFoundResponse(c, errors.New("cannot find specified collection"))
			return
		}

		// [SECURITY] Check permissions
		if !collection.CanListMyVersions(ctxUser) {
			UnauthorizedResponse(c, errors.New("not authorized to list versions"))
			return
		}

		versions, err := ctx.Storage.FindAllActiveVersionsFor(collection.GetId())
		if err != nil {
			logrus.Errorf("Error while trying to list versions for collection id=%v, err: %v", collection.GetId(), err)
			ServerErrorResponse(c, errors.New("cannot list versions, listing error. Check server logs"))
			return
		}

		OKResponse(c, gin.H{
			"versions": versions,
		})
	})
}
