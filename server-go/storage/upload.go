package storage

import (
	"bytes"
	"context"
	"errors"
	"fmt"
	"gocloud.dev/blob"
	"io"
)

func (s *Service) createGPGValidator() func([]byte) error {
	startFound := false
	endingFound := false

	return func(buff []byte) error {
		if bytes.Contains(buff, []byte("-----BEGIN PGP MESSAGE")) {
			startFound = true
		}
		if bytes.Contains(buff, []byte("-----END PGP MESSAGE")) {
			endingFound = true
		}
		return nil
	}
}

func (s *Service) UploadFile(inputStream io.ReadCloser, version UploadedVersion) (bool, error) {
	writeStream, err := s.storage.NewWriter(context.Background(), version.GetTargetPath(), &blob.WriterOptions{})
	if err != nil {
		return false, errors.New(fmt.Sprintf("cannot upload file, attempted to open a writable stream, error: %v", err))
	}

	if writeErr := s.CopyStream(inputStream, writeStream, 1024, s.createGPGValidator()); writeErr != nil {
		return false, errors.New(fmt.Sprintf("cannot upload file, cannot copy stream, error: %v", writeErr))
	}

	return true, nil
}

// CopyStream copies a readable stream to writable stream, while providing a possibility to use a validation callback on-the-fly
func (s *Service) CopyStream(inputStream io.ReadCloser, writeStream io.WriteCloser, bufferLen int, processor func([]byte) error) error {
	p := make([]byte, bufferLen)

	for {
		n, err := inputStream.Read(p)

		if err != nil {
			if err == io.EOF {
				// validation callback
				if processingError := processor(p[:n]); processingError != nil {
					return processingError
				}
				// write to second stream
				_, writeErr := writeStream.Write(p[:n])
				if writeErr != nil {
					return writeErr
				}
			}

			break
		}
		// validation callback
		if processingError := processor(p[:n]); processingError != nil {
			return processingError
		}
		// write to second stream
		_, writeErr := writeStream.Write(p[:n])
		if writeErr != nil {
			return writeErr
		}
	}

	return nil
}
