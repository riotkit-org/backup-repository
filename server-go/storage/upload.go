package storage

import (
	"context"
	"errors"
	"fmt"
	"github.com/sirupsen/logrus"
	"gocloud.dev/blob"
	"io"
)

func (s *Service) UploadFile(inputStream io.ReadCloser, version *UploadedVersion) (int, error) {
	writeStream, err := s.storage.NewWriter(context.Background(), version.GetTargetPath(), &blob.WriterOptions{})
	defer func() {
		writeStream.Close()
	}()

	if err != nil {
		return 0, errors.New(fmt.Sprintf("cannot upload file, attempted to open a writable stream, error: %v", err))
	}

	middlewares := nestedStreamMiddlewares{
		s.createNonEmptyMiddleware(),
		s.createGPGStreamMiddleware(),
	}

	wroteLen, writeErr := s.CopyStream(inputStream, writeStream, 1024*1024, middlewares)
	if writeErr != nil {
		return wroteLen, errors.New(fmt.Sprintf("cannot upload file, cannot copy stream, error: %v", writeErr))
	}

	// Check if file exists at the storage
	_ = writeStream.Close()
	if exists, err := s.storage.Exists(context.TODO(), version.GetTargetPath()); !exists || err != nil {
		logrus.Error(fmt.Sprintf("file was uploaded but does not exists on the storage at path '%v'. Error: %v", version.GetTargetPath(), err))
		return wroteLen, errors.New("storage error")
	}

	// Check if filesize matches buffered stream size
	attributes, err := s.storage.Attributes(context.TODO(), version.GetTargetPath())
	if attributes.Size != int64(wroteLen) {
		logrus.Errorln(fmt.Sprintf("file written to the storage does not match uploaded file, the filesize is not matching %v != %v for file '%v'", wroteLen, attributes.Size, version.GetTargetPath()))
		return wroteLen, errors.New("storage error")
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
