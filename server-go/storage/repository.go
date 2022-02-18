package storage

import (
	"gorm.io/gorm"
)

// VersionsRepository persistence layer for Versions
type VersionsRepository struct {
	db *gorm.DB
}

// findLastHighestVersionNumber Finds latest backup's version number
func (vr VersionsRepository) findLastHighestVersionNumber(collectionId string) (int, error) {
	maxNum := 0
	err := vr.db.Model(&UploadedVersion{}).Select("uploaded_versions.version_number").Where("uploaded_versions.collection_id = ?", collectionId).Order("uploaded_versions.version_number DESC").Limit(1).Find(&maxNum).Error
	if err != nil {
		return 0, err
	}
	return maxNum, nil
}

// findAllVersionsForCollectionId Finds multiple versions for given collection by it's id
func (vr VersionsRepository) findAllVersionsForCollectionId(collectionId string) ([]UploadedVersion, error) {
	var foundVersions []UploadedVersion

	err := vr.db.Model(&UploadedVersion{}).Where("uploaded_versions.collection_id = ?", collectionId).Order("uploaded_versions.version_number DESC").Find(&foundVersions).Error
	if err != nil {
		return []UploadedVersion{}, err
	}
	return foundVersions, nil
}

// delete Deletes entry from database
func (vr VersionsRepository) delete(version *UploadedVersion) (error, bool) {
	var result bool
	return vr.db.Model(&UploadedVersion{}).Where("uploaded_versions.id = ?", version.Id).Delete(&result).Error, result
}

// create Creates an entry in database
func (vr VersionsRepository) create(version *UploadedVersion) error {
	return vr.db.Create(version).Error
}

// InitializeModel connects model to migrations
func InitializeModel(db *gorm.DB) error {
	return db.AutoMigrate(&UploadedVersion{})
}
