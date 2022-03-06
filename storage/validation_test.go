package storage

import (
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestQuotaMaxFileSizeMiddleware(t *testing.T) {
	s := Service{}
	middleware := s.createQuotaMaxFileSizeMiddleware(int64(1024))

	// below limit
	assert.Nil(t, middleware.processor([]byte("current-chunk"), int64(0), []byte(""), 1))
	assert.Nil(t, middleware.processor([]byte("current-chunk"), int64(600), []byte(""), 1))

	// above limit
	assert.Equal(t, "filesize reached allowed limit. Uploaded 1025 bytes, allowed to upload only 1024 bytes", middleware.processor([]byte("current-chunk"), int64(1025), []byte(""), 1).Error())
}

func TestGPGStreamMiddleware(t *testing.T) {
	s := Service{}
	middleware := s.createGPGStreamMiddleware()

	// beginning
	assert.Nil(t, middleware.processor([]byte("-----BEGIN PGP MESSAGE .......hello......"), int64(0), []byte(""), 1))
	assert.NotNil(t, middleware.processor([]byte("hello"), int64(0), []byte(""), 1))

	// ending
	assert.Nil(t, middleware.processor([]byte("-----END PGP MESSAGE"), int64(0), []byte("previous-hunk"), 161)) // previous-hunk is non-empty, when END OF STREAM is happening
	assert.NotNil(t, middleware.processor([]byte("-------broken-ending"), int64(0), []byte("previous-hunk"), 161))
}
