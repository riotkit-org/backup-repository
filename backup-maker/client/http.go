package client

import (
	"net/http"
	"time"
)

type HTTPClient interface {
	Do(req *http.Request) (*http.Response, error)
	SetTimeout(timeout int)
}

type HTTPClientImpl struct {
	client  http.Client
	Timeout int
}

func (that HTTPClientImpl) SetTimeout(timeout int) {
	that.client.Timeout = time.Second * time.Duration(timeout)
}

func (that HTTPClientImpl) Do(req *http.Request) (response *http.Response, reterr error) {
	return that.client.Do(req)
}

func CreateHttpClient() HTTPClient {
	return HTTPClientImpl{
		client:  http.Client{},
		Timeout: 30,
	}
}

//
// for testing purposes
//

type HttpClientMock struct {
	mockedResponse http.Response
	mockedError    error
}

func (h HttpClientMock) Do(_ *http.Request) (response *http.Response, reterr error) {
	return &h.mockedResponse, h.mockedError
}
func (h HttpClientMock) SetTimeout(_ int) {}
