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

func (g GrantedAccessRepository) create(access *GrantedAccess) error {
	return g.db.Model(&GrantedAccess{}).Create(&access).Error
}

func (g GrantedAccessRepository) getGrantedAccessByHashedToken(hashedToken interface{}) (GrantedAccess, error) {
	var gaModel GrantedAccess

	if result := g.db.Model(&GrantedAccess{}).First(&gaModel, "id = ?", hashedToken); result.Error != nil {
		logrus.Debugf("Cannot find GrantedAccess id=%v, error: %v", hashedToken, result.Error)

		return gaModel, result.Error
	}

	return gaModel, nil
}

func (g GrantedAccessRepository) checkSessionExistsById(id string) bool {
	var exists bool

	if err := g.db.Model(&GrantedAccess{}).Select("count(*) > 0").Where("id = ?", id).Find(&exists).Error; err != nil {
		logrus.Errorln("checkSessionExistsById(): %v", err)
	}

	return exists
}

func (g GrantedAccessRepository) revokeById(id string) error {
	return g.db.Model(&GrantedAccess{}).Where("id = ?", id).Update("deactivated", true).Error
}

func (g GrantedAccessRepository) findForUsername(name string) []GrantedAccess {
	var result []GrantedAccess
	g.db.Model(&GrantedAccess{}).Where("granted_accesses.user = ?", name).Order("created_at desc").Limit(100).Find(&result)

	return result
}

func (g GrantedAccessRepository) findOneBySessionId(id string) (GrantedAccess, error) {
	var result GrantedAccess
	return result, g.db.Model(&GrantedAccess{}).Where("granted_accesses.id = ?", id).Limit(1).Find(&result).Error
}

func InitializeModel(db *gorm.DB) error {
	return db.AutoMigrate(&GrantedAccess{})
}
