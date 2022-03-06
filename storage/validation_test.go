package storage

import (
	"github.com/stretchr/testify/assert"
	"golang.org/x/net/context"
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

	assert.Nil(t, middleware.resultReporter()) // this should do nothing
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

	assert.Nil(t, middleware.resultReporter()) // this should do nothing
}

func TestNonEmptyMiddleware(t *testing.T) {
	s := Service{}
	middleware := s.createNonEmptyMiddleware()

	// at the beginning it will raise error
	assert.NotNil(t, middleware.resultReporter())

	// after processing an empty chunk it will still report error
	_ = middleware.processor([]byte(""), int64(0), []byte(""), 1)
	assert.NotNil(t, middleware.resultReporter())

	// but after processing at least one non-empty chunk it will be fine
	_ = middleware.processor([]byte("hello"), int64(5), []byte(""), 1)
	assert.Nil(t, middleware.resultReporter())
}

func TestRequestCancelledMiddleware(t *testing.T) {
	s := Service{}

	ctx, cancel := context.WithCancel(context.TODO())
	cancel()

	// cancelled
	middlewareCanceled := s.createRequestCancelledMiddleware(ctx)
	assert.NotNil(t, middlewareCanceled.processor([]byte("hello"), int64(5), []byte(""), 1))

	// not cancelled
	middlewareNotCancelled := s.createRequestCancelledMiddleware(context.TODO())
	assert.Nil(t, middlewareNotCancelled.processor([]byte("hello"), int64(5), []byte(""), 1))

	assert.Nil(t, middlewareNotCancelled.resultReporter()) // this should do nothing
}

func TestNestedStreamMiddlewares(t *testing.T) {
	s := Service{}

	middlewares := NestedStreamMiddlewares{
		s.createNonEmptyMiddleware(),
		s.createQuotaMaxFileSizeMiddleware(int64(1024)),
	}

	_ = middlewares.processChunk([]byte(""), int64(0), []byte(""), 1)
	assert.NotNil(t, middlewares.checkFinalStatusAfterFilesWasUploaded())
	assert.Nil(t, middlewares.processChunk([]byte("hello"), int64(5), []byte(""), 1))
	assert.Nil(t, middlewares.checkFinalStatusAfterFilesWasUploaded())
}
