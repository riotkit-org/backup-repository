package storage

import (
	"context"
	"errors"
	"fmt"
	"github.com/sirupsen/logrus"
	"gocloud.dev/blob"
	"io"
)

func (s *Service) UploadFile(parentCtx context.Context, inputStream io.ReadCloser, version *UploadedVersion, middlewares *NestedStreamMiddlewares) (int64, error) {
	ctx, cancellation := context.WithTimeout(parentCtx, s.IOTimeout)
	defer cancellation()

	writeStream, err := s.storage.NewWriter(ctx, version.GetTargetPath(), &blob.WriterOptions{})
	defer func() { _ = writeStream.Close() }()

	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot upload file, attempted to open a writable stream, error: %v", err))
	}

	wroteLen, writeErr := s.CopyStream(ctx, inputStream, writeStream, 1024*1024, middlewares)
	if writeErr != nil {
		return wroteLen, errors.New(fmt.Sprintf("cannot upload file, cannot copy stream, error: %v", writeErr))
	}

	// Check if file exists at the storage
	_ = writeStream.Close()
	if exists, err := s.storage.Exists(ctx, version.GetTargetPath()); !exists || err != nil {
		logrus.Error(fmt.Sprintf("file was uploaded but does not exists on the storage at path '%v'. IsError: %v", version.GetTargetPath(), err))
		return wroteLen, errors.New("storage error")
	}

	// Check if filesize matches buffered stream size
	attributes, err := s.storage.Attributes(ctx, version.GetTargetPath())
	if attributes.Size != wroteLen {
		logrus.Errorln(fmt.Sprintf("file written to the storage does not match uploaded file, the filesize is not matching %v != %v for file '%v'", wroteLen, attributes.Size, version.GetTargetPath()))
		return wroteLen, errors.New("storage error")
	}

	return wroteLen, nil
}

// CopyStream copies a readable stream to writable stream, while providing a possibility to use a validation callbacks on-the-fly
func (s *Service) CopyStream(ctx context.Context, inputStream io.ReadCloser, writeStream io.Writer, bufferLen int, middlewares *NestedStreamMiddlewares) (int64, error) {
	buff := make([]byte, bufferLen)
	previousBuff := make([]byte, bufferLen)
	var totalLength int64
	chunkNum := 0

	for {
		select {
		case <-ctx.Done():
			deadline, _ := ctx.Deadline()
			logrus.Errorf("Upload hit a context cancellation: %s, deadline: %s", ctx.Err(), deadline)
			return totalLength, errors.New("context deadline exceeded, probably hit a Storage I/O timeout or Request timeout")

		default:
			n, err := inputStream.Read(buff)
			chunkNum += 1

			if err != nil {
				if err == io.EOF {
					totalLength += int64(len(buff[:n]))

					// validation callbacks
					if err := middlewares.processChunk(buff[:n], totalLength, previousBuff, chunkNum); err != nil {
						return totalLength, err
					}

					// write to target stream (copy)
					if _, writeErr := writeStream.Write(buff[:n]); writeErr != nil {
						return totalLength, writeErr
					}

					return totalLength, nil
				}

				return totalLength, errors.New(fmt.Sprintf("cannot copy stream, error: %v", err))
			}

			totalLength += int64(len(buff[:n]))
			previousBuff = buff[:n]

			// validation callbacks
			if err := middlewares.processChunk(buff[:n], totalLength, []byte(""), chunkNum); err != nil {
				return totalLength, err
			}

			// write to target stream (copy)
			_, writeErr := writeStream.Write(buff[:n])
			if writeErr != nil {
				return totalLength, writeErr
			}
			if err := middlewares.checkFinalStatusAfterFilesWasUploaded(); err != nil {
				return totalLength, err
			}
		}
	}
}
