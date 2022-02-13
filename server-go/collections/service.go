package collections

import "github.com/riotkit-org/backup-repository/config"

type Service struct {
	repository collectionRepository
}

func (s *Service) GetCollectionById(id string) (*Collection, error) {
	return s.repository.getById(id)
}

func NewService(config config.ConfigurationProvider) Service {
	return Service{
		repository: collectionRepository{
			config: config,
		},
	}
}
