package health

import (
	"encoding/json"
	"fmt"
	"github.com/riotkit-org/backup-repository/storage"
	"reflect"
)

type Validator interface {
	Validate() error
}
type Validators []Validator

func (v Validators) Validate() StatusCollection {
	var status StatusCollection

	for _, validator := range v {
		if err := validator.Validate(); err != nil {
			status = append(status, Status{
				Name:      reflect.TypeOf(validator).Name(),
				StatusMsg: err.Error(),
				IsError:   true,
			})
		} else {
			status = append(status, Status{
				Name:      reflect.TypeOf(validator).Name(),
				StatusMsg: "OK",
				IsError:   false,
			})
		}
	}

	return status
}

type Status struct {
	Name      string
	StatusMsg string
	IsError   bool
}

func (s *Status) MarshalJSON() ([]byte, error) {
	curr := make(map[string]interface{})
	curr["name"] = s.Name
	curr["statusText"] = fmt.Sprintf("%v=%v", s.Name, !s.IsError)
	curr["status"] = !s.IsError
	curr["message"] = s.StatusMsg

	return json.Marshal(curr)
}

type StatusCollection []Status

func (sc StatusCollection) GetOverallStatus() bool {
	for _, status := range sc {
		if status.IsError {
			return false
		}
	}
	return true
}

type StorageInterface interface {
	FindLatestVersion(collectionId string) (storage.UploadedVersion, error)
}
