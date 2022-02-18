package storage

import (
	"gorm.io/gorm"
)

type VersionsRepository struct {
	db *gorm.DB
}

func (vr VersionsRepository) findLastHighestVersionNumber(collectionId string) (int, error) {
	maxNum := 0
	err := vr.db.Model(&UploadedVersion{}).Select("uploaded_versions.version_number").Where("uploaded_versions.collection_id = ?", collectionId).Order("uploaded_versions.version_number DESC").Limit(1).Find(&maxNum).Error
	if err != nil {
		return 0, err
	}
	return maxNum, nil
}

func (vr VersionsRepository) findAllVersionsForCollectionId(collectionId string) ([]UploadedVersion, error) {
	var foundVersions []UploadedVersion

	err := vr.db.Model(&UploadedVersion{}).Where("uploaded_versions.collection_id = ?", collectionId).Order("uploaded_versions.version_number DESC").Find(&foundVersions).Error
	if err != nil {
		return []UploadedVersion{}, err
	}
	return foundVersions, nil
}

func InitializeModel(db *gorm.DB) error {
	return db.AutoMigrate(&UploadedVersion{})
}
