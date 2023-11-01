package storage

import (
	"bytes"
	"context"
	"errors"
	"fmt"
	"github.com/sirupsen/logrus"
)

//
// Stream middleware is a pair of callbacks that are invoked during buffering and after finished buffering
// of streamed upload
//

type streamMiddleware struct {
	// []byte - current buffer value
	// int64  - total read size till this moment
	// []byte - if current buffer is an END OF STREAM, then this parameter will contain previous hunk,
	//          so you can join previous+last to have full information in case, when last hunk would be too small
	// int    - processed chunk number
	processor      func([]byte, int64, []byte, int) error
	resultReporter func() error
}

//
// Aggregation of middlewares
//

type NestedStreamMiddlewares []streamMiddleware

func (nv NestedStreamMiddlewares) processChunk(chunk []byte, processedTotalBytes int64, previousHunkBeforeEof []byte, chunkNum int) error {
	for _, processor := range nv {
		if processingError := processor.processor(chunk, processedTotalBytes, previousHunkBeforeEof, chunkNum); processingError != nil {
			return processingError
		}
	}
	return nil
}

func (nv NestedStreamMiddlewares) checkFinalStatusAfterFilesWasUploaded() error {
	for _, processor := range nv {
		if processingError := processor.resultReporter(); processingError != nil {
			return processingError
		}
	}
	return nil
}

//
// Validators
//

// createGPGStreamMiddleware Checks if stream is a valid GPG encrypted file by checking GPG header and footer
func (s *Service) createGPGStreamMiddleware() streamMiddleware {
	validator := func(buff []byte, totalLength int64, previousHunkBeforeEof []byte, chunkNum int) error {
		if chunkNum == 1 && !bytes.Contains(buff, []byte("-----BEGIN PGP MESSAGE")) {
			return errors.New("first chunk of uploaded data does not contain a valid GPG header")
		}

		// if previous hunk is not empty, then we are at the end of the stream
		if len(previousHunkBeforeEof) > 0 {
			concatenated := [][]byte{previousHunkBeforeEof, buff}

			if !bytes.Contains(bytes.Join(concatenated, []byte("")), []byte("-----END PGP MESSAGE")) {
				return errors.New("end of stream does not contain a valid GPG footer, suspecting a data corruption")
			}
		}
		return nil
	}

	resultReporter := func() error {
		return nil
	}

	return streamMiddleware{processor: validator, resultReporter: resultReporter}
}

// createNonEmptyMiddleware Checks if anything was sent at all
func (s *Service) createNonEmptyMiddleware() streamMiddleware {
	var recordedTotalLength int64

	validator := func(buff []byte, totalLength int64, previousHunkBeforeEof []byte, chunkNum int) error {
		recordedTotalLength = totalLength
		return nil
	}

	resultReporter := func() error {
		if recordedTotalLength == 0 {
			return errors.New("sent empty data")
		}

		return nil
	}

	return streamMiddleware{processor: validator, resultReporter: resultReporter}
}

// createQuotaMaxFileSizeMiddleware Takes care about the maximum allowed filesize limit
func (s *Service) createQuotaMaxFileSizeMiddleware(maxFileSize int64) streamMiddleware {
	validator := func(buff []byte, totalLength int64, previousHunkBeforeEof []byte, chunkNum int) error {
		if totalLength > maxFileSize {
			return errors.New(fmt.Sprintf("filesize reached allowed limit. Uploaded %v bytes, allowed to upload only %v bytes", totalLength, maxFileSize))
		}
		return nil
	}

	return streamMiddleware{processor: validator, resultReporter: func() error {
		return nil
	}}
}

// createRequestCancelledMiddleware handles the request cancellation
func (s *Service) createRequestCancelledMiddleware(context context.Context) streamMiddleware {
	return streamMiddleware{
		processor: func(i []byte, i2 int64, i3 []byte, i4 int) error {
			if context.Err() != nil {
				logrus.Warning(fmt.Sprintf("Upload was cancelled: %v", context.Err()))
				return errors.New("upload was cancelled")
			}
			return nil
		},
		resultReporter: func() error {
			return nil
		},
	}
}
