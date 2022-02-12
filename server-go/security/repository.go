package security

import (
	"github.com/sirupsen/logrus"
	"gorm.io/gorm"
)

const KindGrantedAccess = "grantedaccesses"

//
// GrantedAccess
//

type GrantedAccessRepository struct {
	db *gorm.DB
}

func (g GrantedAccessRepository) create(access *GrantedAccess) {
	g.db.Model(&GrantedAccess{}).Create(&access)
}

func (g GrantedAccessRepository) getGrantedAccessByHashedToken(hashedToken interface{}) (GrantedAccess, error) {
	var gaModel GrantedAccess

	if result := g.db.Model(&GrantedAccess{}).First(&gaModel, "id = ?", hashedToken); result.Error != nil {
		logrus.Debugf("Cannot find GrantedAccess id=%v, error: %v", hashedToken, result.Error)

		return gaModel, result.Error
	}

	return gaModel, nil
}

func (g GrantedAccessRepository) revokeById(id string) error {
	return g.db.Model(&GrantedAccess{}).Where("id = ?", id).Update("deactivated", true).Error
}

func InitializeModel(db *gorm.DB) error {
	return db.AutoMigrate(&GrantedAccess{})
}
