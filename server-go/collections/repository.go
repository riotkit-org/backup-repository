package collections

import (
	"encoding/json"
	"errors"
	"fmt"
	"github.com/riotkit-org/backup-repository/config"
	"github.com/sirupsen/logrus"
)

const KindCollection = "backupcollections"

type collectionRepository struct {
	config config.ConfigurationProvider
}

// getById Returns a `kind: BackupCollection` object by it's `metadata.name`
func (c *collectionRepository) getById(id string) (*Collection, error) {
	doc, retrieveErr := c.config.GetSingleDocument(KindCollection, id)
	result := Collection{}

	if retrieveErr != nil {
		return &result, errors.New(fmt.Sprintf("error retrieving result: %v", retrieveErr))
	}

	if err := json.Unmarshal([]byte(doc), &result); err != nil {
		logrus.Debugln(doc)
		return &Collection{}, errors.New(fmt.Sprintf("cannot unmarshal response fron Kubernetes to get collection of id=%v", id))
	}

	return &result, nil
}
