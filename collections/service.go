package collections

import (
	"fmt"
	"github.com/riotkit-org/backup-repository/config"
	"github.com/sirupsen/logrus"
	"time"
)

type Service struct {
	repository collectionRepository
}

func (s *Service) GetCollectionById(id string) (*Collection, error) {
	return s.repository.getById(id)
}

func (s *Service) ValidateIsBackupWindowAllowingToUpload(collection *Collection, contextTime time.Time) bool {
	// no defined Backup Windows = no limits, ITS OPTIONAL
	if len(collection.Spec.Windows) == 0 {
		return true
	}

	for _, window := range collection.Spec.Windows {
		result, err := window.IsInWindowNow(contextTime)

		if err != nil {
			logrus.Error(fmt.Sprintf("Backup Window validation error (collection id=%v): %v", collection.Metadata.Name, err))
		}

		if result {
			return true
		}
	}

	return false
}

func NewService(config config.ConfigurationProvider) Service {
	return Service{
		repository: collectionRepository{
			config: config,
		},
	}
}
