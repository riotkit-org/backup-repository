package storage

import (
	"github.com/google/uuid"
	"github.com/riotkit-org/backup-repository/collections"
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

func CreateNewVersionFromCollection(c collections.Collection, svc Service, uploader string, uploaderSessionId string, filesize int) UploadedVersion {
	nextVersion := svc.FindNextVersionForCollectionId(c.Metadata.Name)

	return UploadedVersion{
		Id:                  uuid.New().String(),
		CollectionId:        c.Metadata.Name,
		VersionNumber:       nextVersion,
		Filename:            c.GenerateNextVersionFilename(nextVersion),
		Filesize:            filesize,
		UploadedBySessionId: uploaderSessionId,
		Uploader:            uploader,
		CreatedAt:           time.Time{},
		UpdatedAt:           time.Time{},
		DeletedAt:           gorm.DeletedAt{},
	}
}
