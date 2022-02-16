package storage

import (
	"context"
	"errors"
	"fmt"
	"gocloud.dev/blob"
	"io"
)

func (s *Service) UploadFile(inputStream io.ReadCloser, version *UploadedVersion) (int, error) {
	writeStream, err := s.storage.NewWriter(context.Background(), version.GetTargetPath(), &blob.WriterOptions{})
	defer writeStream.Close()

	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot upload file, attempted to open a writable stream, error: %v", err))
	}

	middlewares := nestedStreamMiddlewares{
		// todo: add a middleware to abort file upload if filesize reached the limit
		s.createNonEmptyMiddleware(),
		s.createGPGStreamMiddleware(),
	}

	wroteLen, writeErr := s.CopyStream(inputStream, writeStream, 1024, middlewares)
	if writeErr != nil {
		return wroteLen, errors.New(fmt.Sprintf("cannot upload file, cannot copy stream, error: %v", writeErr))
	}

	return wroteLen, nil
}

// CopyStream copies a readable stream to writable stream, while providing a possibility to use a validation callbacks on-the-fly
func (s *Service) CopyStream(inputStream io.ReadCloser, writeStream io.WriteCloser, bufferLen int, middlewares nestedStreamMiddlewares) (int, error) {
	buff := make([]byte, bufferLen)
	previousBuff := make([]byte, bufferLen)
	totalLength := 0
	chunkNum := 0

	for {
		n, err := inputStream.Read(buff)
		chunkNum += 1

		if err != nil {
			if err == io.EOF {
				totalLength += len(buff[:n])

				// validation callbacks
				if err := middlewares.processChunk(buff[:n], totalLength, previousBuff, chunkNum); err != nil {
					return totalLength, err
				}

				// write to second stream
				if _, writeErr := writeStream.Write(buff[:n]); writeErr != nil {
					return totalLength, writeErr
				}

				break
			}

			return totalLength, errors.New(fmt.Sprintf("cannot copy stream, error: %v", err))
		}

		totalLength += len(buff[:n])
		previousBuff = buff[:n]

		// validation callbacks
		if err := middlewares.processChunk(buff[:n], totalLength, []byte(""), chunkNum); err != nil {
			return totalLength, err
		}
		// write to second stream
		_, writeErr := writeStream.Write(buff[:n])
		if writeErr != nil {
			return totalLength, writeErr
		}
		if err := middlewares.checkFinalStatusAfterFilesWasUploaded(); err != nil {
			return totalLength, err
		}
	}

	return totalLength, nil
}
