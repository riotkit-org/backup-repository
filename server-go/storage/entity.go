package storage

import (
	"gorm.io/gorm"
	"time"
)

type UploadedVersion struct {
	Id            string `json:"id" structs:"id" sql:"type:string;primary_key;default:uuid_generate_v4()`
	CollectionId  string `json:"collectionId"`
	VersionNumber int    `json:"versionNumber"`
	Filename      string `json:"filename"` // full filename e.g. iwa-ait-v1-db.tar.gz
	Filesize      int    `json:"filesize"` // in bytes

	// auditing
	UploadedBySessionId string `json:"uploadedBySessionId"`
	Uploader            string `json:"user"        structs:"user"`
	CreatedAt           time.Time
	UpdatedAt           time.Time
	DeletedAt           gorm.DeletedAt `gorm:"index"`
}

func (u *UploadedVersion) GetTargetPath() string {
	return u.CollectionId + "/" + u.Filename
}
