package http

import (
	"bytes"
	"encoding/json"
	"github.com/gin-gonic/gin"
	"github.com/sirupsen/logrus"
	"strings"
)

type errorLogWriter struct {
	gin.ResponseWriter
	body *bytes.Buffer
}

func (w errorLogWriter) Write(b []byte) (int, error) {
	if w.Status() > 299 && strings.Contains(w.Header().Get("Content-Type"), "application/json") {
		w.body.Write(b)
	}
	return w.ResponseWriter.Write(b)
}

func responseErrorLoggerMiddleware() gin.HandlerFunc {
	return func(c *gin.Context) {
		blw := &errorLogWriter{body: bytes.NewBufferString(""), ResponseWriter: c.Writer}
		c.Writer = blw
		c.Next()
		if len(blw.body.String()) > 0 {
			response := GenericResponseType{}
			unmarshalErr := json.Unmarshal(blw.body.Bytes(), &response)
			if unmarshalErr != nil {
				logrus.Warningln("Cannot parse error response. Probably not in valid format")
				return
			}
			logrus.Errorf("Returned '%s' error, message: '%s'", response.Error, response.Message)
		}
	}
}
