package http

import (
	"github.com/gin-gonic/gin"
	"github.com/riotkit-org/backup-repository/core"
)

func SpawnHttpApplication(ctx core.ApplicationContainer) {
	r := gin.Default()

	addLookupUserRoute(r, ctx)

	_ = r.Run()
}
