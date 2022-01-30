package http

import "github.com/gin-gonic/gin"

func SpawnHttpApplication() {
	r := gin.Default()
	addLookupUserRoute(r)
	_ = r.Run()
}
