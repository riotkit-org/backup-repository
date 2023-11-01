package db

import (
	"fmt"
	"github.com/riotkit-org/backup-repository/pkg/concurrency"
	"github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/riotkit-org/backup-repository/pkg/storage"
	"github.com/sirupsen/logrus"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

func CreateDatabaseDriver(hostname string, user string, password string, dbname string, port int, additionalDSN string) (*gorm.DB, error) {
	dsn := fmt.Sprintf("host=%v user=%v password=%v dbname=%v port=%v %v",
		hostname, user, password, dbname, port, additionalDSN)

	return gorm.Open(postgres.Open(dsn), &gorm.Config{})
}

func InitializeDatabase(db *gorm.DB) bool {
	if err := security.InitializeModel(db); err != nil {
		logrus.Errorf("Cannot initialize GrantedAccess model: %v", err)
		return false
	}
	if err := storage.InitializeModel(db); err != nil {
		logrus.Errorf("Cannot initialize UploadedVersion model: %v", err)
		return false
	}
	if err := concurrency.InitializeModel(db); err != nil {
		logrus.Errorf("Cannot initialize Locks model: %v", err)
		return false
	}

	return true
}
