package security_test

import (
	"github.com/riotkit-org/backup-repository/pkg/security"
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestCanThoseRolesPerformAction(t *testing.T) {
	// Multiple levels - a role is expanded two times
	// RoleSysAdmin -> RoleCollectionManager -> RoleBackupDownloader -> [ActionDownload]
	assert.Equal(t, true, security.CanThoseRolesPerformAction([]string{
		security.RoleSysAdmin,
	}, security.ActionDownload))

	// Two levels - expanded one time
	assert.Equal(t, true, security.CanThoseRolesPerformAction([]string{
		security.RoleCollectionManager,
	}, security.ActionDownload))

	// Direct role
	assert.Equal(t, true, security.CanThoseRolesPerformAction([]string{
		security.RoleBackupDownloader,
	}, security.ActionDownload))

	// RoleBackupUploader ! -> ActionDownload
	assert.Equal(t, false, security.CanThoseRolesPerformAction([]string{
		security.RoleBackupUploader,
	}, security.ActionDownload))
}

type TestActor struct {
	name                       string
	isInAccessKeyContext       bool
	roles                      security.Roles
	accessTokenContextualRoles security.Roles
	jwtScopeLimitations        *security.SessionLimitedOperationsScope
}

func (a *TestActor) IsInAccessKeyContext() bool {
	return a.isInAccessKeyContext
}

func (a *TestActor) GetAccessKeyRolesInContextOf(subject security.Subject) security.Roles {
	return a.accessTokenContextualRoles
}

func (a *TestActor) GetRoles() security.Roles {
	return a.roles
}

func (a *TestActor) GetEmail() string {
	return ""
}

func (a *TestActor) GetName() string {
	return a.name
}

func (a *TestActor) GetTypeName() string {
	return "user"
}

func (a *TestActor) GetSessionLimitedOperationsScope() *security.SessionLimitedOperationsScope {
	return a.jwtScopeLimitations
}

type FakeSubject struct {
	id       string
	typeName string
	acl      *security.AccessControlList
}

func (fs *FakeSubject) GetId() string {
	return fs.id
}

func (fs *FakeSubject) GetTypeName() string {
	return fs.typeName
}

func (fs *FakeSubject) GetAccessControlList() *security.AccessControlList {
	return fs.acl
}

func TestDecideCanDo_AsSysAdminCanDoEverything(t *testing.T) {
	actor := TestActor{
		name:                 "bakunin",
		isInAccessKeyContext: false,
		roles:                security.Roles{security.RoleSysAdmin},
		jwtScopeLimitations:  &security.SessionLimitedOperationsScope{Elements: []security.ScopedElement{}},
	}

	for _, action := range security.AllActions {
		assert.True(t, security.DecideCanDo(&security.DecisionRequest{
			Actor:   &actor,
			Subject: nil,
			Action:  action,
		}))
	}
}

func TestDecideCanDo_AsSysAdminCantDoActionWhenJWTForbids(t *testing.T) {
	actor := &TestActor{
		name:                 "bakunin",
		isInAccessKeyContext: false,

		// the user is ADMIN. Can do everything
		roles: security.Roles{security.RoleSysAdmin},

		// no access token limitations are applied
		accessTokenContextualRoles: security.Roles{},

		// JWT token is limited to only DOWNLOAD
		jwtScopeLimitations: &security.SessionLimitedOperationsScope{Elements: []security.ScopedElement{
			{Type: "collection", Name: "iwa-ait", Roles: security.Roles{security.RoleBackupDownloader}},
		}},
	}
	subject := &FakeSubject{
		id:       "iwa-ait",
		typeName: "collection",
		acl: &security.AccessControlList{
			security.AccessControlObject{
				Name: "bakunin",
				Type: "user",

				// the collection allows user to UPLOAD & DOWNLOAD explicitly
				Roles: security.Roles{
					security.RoleBackupDownloader,
					security.RoleBackupUploader,
				},
			},
		},
	}

	// CAN'T do: /auth/login generated a JWT that allows only to DOWNLOAD
	assert.False(t, security.DecideCanDo(&security.DecisionRequest{
		Actor:   actor,
		Subject: subject,
		Action:  security.ActionUpload,
	}))

	// CAN DO: download is allowed by JWT limitations
	assert.True(t, security.DecideCanDo(&security.DecisionRequest{
		Actor:   actor,
		Subject: subject,
		Action:  security.ActionDownload,
	}))
}

func TestDecideCanDo_AsSysAdminCantDoActionWhenAccessTokenForbids(t *testing.T) {
	actor := &TestActor{
		name:                 "bakunin",
		isInAccessKeyContext: true,

		// the user is ADMIN. Can do everything
		roles: security.Roles{security.RoleSysAdmin},

		// ACCESS TOKEN is limiting roles
		accessTokenContextualRoles: security.Roles{
			security.RoleBackupDownloader,
		},

		// JWT token is NOT limiting anything
		jwtScopeLimitations: &security.SessionLimitedOperationsScope{Elements: []security.ScopedElement{}},
	}
	subject := &FakeSubject{
		id:       "iwa-ait",
		typeName: "collection",
		acl: &security.AccessControlList{
			security.AccessControlObject{
				Name: "bakunin",
				Type: "user",

				// the collection allows user to UPLOAD & DOWNLOAD explicitly
				Roles: security.Roles{
					security.RoleBackupDownloader,
					security.RoleBackupUploader,
				},
			},
		},
	}

	// CAN'T do: user logged in with ACCESS TOKEN that limits action to DOWNLOAD only
	assert.False(t, security.DecideCanDo(&security.DecisionRequest{
		Actor:   actor,
		Subject: subject,
		Action:  security.ActionUpload,
	}))

	// CAN DO: download is allowed
	assert.True(t, security.DecideCanDo(&security.DecisionRequest{
		Actor:   actor,
		Subject: subject,
		Action:  security.ActionDownload,
	}))
}

func TestDecideCanDo_AsCollectionManagerICanManageCollection(t *testing.T) {
	actor := &TestActor{
		name:                 "bakunin",
		isInAccessKeyContext: false,

		// No global roles at all
		roles: security.Roles{},

		// No contextual limitations at all
		accessTokenContextualRoles: security.Roles{},
		jwtScopeLimitations:        &security.SessionLimitedOperationsScope{Elements: []security.ScopedElement{}},
	}
	subject := &FakeSubject{
		id:       "iwa-ait",
		typeName: "collection",
		acl: &security.AccessControlList{
			security.AccessControlObject{
				Name: "bakunin",
				Type: "user",

				// User is a Collection Manager in this collection
				Roles: security.Roles{
					security.RoleCollectionManager,
				},
			},
		},
	}

	for _, role := range []string{security.ActionUpload, security.ActionUploadAnytime, security.ActionDownload} {
		assert.True(t, security.DecideCanDo(&security.DecisionRequest{
			Actor:   actor,
			Subject: subject,
			Action:  role,
		}))
	}
}
