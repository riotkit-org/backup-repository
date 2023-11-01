package security

const (
	RoleUserManager       = "userManager"
	RoleCollectionManager = "collectionManager"
	RoleBackupDownloader  = "backupDownloader"
	RoleBackupUploader    = "backupUploader"

	// RoleUploadsAnytime allows uploading versions regardless of Backup Windows
	RoleUploadsAnytime = "uploadsAnytime"
	RoleSysAdmin       = "systemAdmin"
)

const (
	ActionDownload      = "file.download"
	ActionUpload        = "file.upload"
	ActionUploadAnytime = "file.upload-anytime"
	ActionViewProfile   = "profile.view"
)

var AllActions = []string{
	ActionDownload, ActionUpload, ActionUploadAnytime, ActionViewProfile,
}

func GetRolesInheritance() map[string][]string {
	return map[string][]string{
		RoleSysAdmin:          {RoleUserManager, RoleCollectionManager},
		RoleCollectionManager: {RoleBackupDownloader, RoleBackupDownloader, RoleUploadsAnytime},
	}
}

func GetRolesActions() map[string][]string {
	return map[string][]string{
		RoleBackupDownloader: {ActionDownload},
		RoleBackupUploader:   {ActionUpload},
		RoleUploadsAnytime:   {ActionUpload, ActionUploadAnytime},
		RoleUserManager:      {ActionViewProfile},
	}
}

const (
	IdentityKeyClaimIndex = "login"
	AccessKeyClaimIndex   = "accessKeyName"
	ScopeClaimIndex       = "operationsScope"
)
