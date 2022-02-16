package storage

import (
	"bytes"
	"errors"
)

//
// Stream middleware is a pair of callbacks that are invoked during buffering and after finished buffering
// of streamed upload
//

type streamMiddleware struct {
	// []byte - current buffer value
	// int    - total read size till this moment
	// []byte - if current buffer is an END OF STREAM, then this parameter will contain previous hunk,
	//          so you can join previous+last to have full information in case, when last hunk would be too small
	// int    - processed chunk number
	processor      func([]byte, int, []byte, int) error
	resultReporter func() error
}

//
// Aggregation of middlewares
//

type nestedStreamMiddlewares []streamMiddleware

func (nv nestedStreamMiddlewares) processChunk(chunk []byte, processedTotalBytes int, previousHunkBeforeEof []byte, chunkNum int) error {
	for _, processor := range nv {
		if processingError := processor.processor(chunk, processedTotalBytes, previousHunkBeforeEof, chunkNum); processingError != nil {
			return processingError
		}
	}
	return nil
}

func (nv nestedStreamMiddlewares) checkFinalStatusAfterFilesWasUploaded() error {
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
	validator := func(buff []byte, totalLength int, previousHunkBeforeEof []byte, chunkNum int) error {
		if chunkNum == 1 && !bytes.Contains(buff, []byte("-----BEGIN PGP MESSAGE")) {
			return errors.New("first chunk of uploaded data does not contain a valid GPG header")
		}

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
	recordedTotalLength := 0

	validator := func(buff []byte, totalLength int, previousHunkBeforeEof []byte, chunkNum int) error {
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
