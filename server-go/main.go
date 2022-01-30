package main

import (
	"github.com/riotkit-org/backup-repository/http"
)

func main() {
	// todo: First thread - HTTP
	// todo: Second thread - configuration changes watcher
	//       Notice: Fork configuration objects on each request? Or do not allow updating, when any request is pending?
	http.SpawnHttpApplication()
}
