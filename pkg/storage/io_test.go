package storage

import (
	"bytes"
	"context"
	"github.com/stretchr/testify/assert"
	"io"
	"strings"
	"testing"
	"time"
)

// Basic test for reading small portion of data
func TestService_CopyStream(t *testing.T) {
	s := Service{}

	ctx, _ := context.WithTimeout(context.TODO(), time.Second*5)

	readStream := io.NopCloser(strings.NewReader("hello-world"))
	var writeStream bytes.Buffer
	buff := make([]byte, 11)

	_, _ = s.CopyStream(ctx, readStream, &writeStream, 1024, &NestedStreamMiddlewares{})
	_, _ = writeStream.Read(buff)

	assert.Equal(t, "hello-world", string(buff))
}
