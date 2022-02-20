Backup Repository
=================

Cloud-native, zero-knowledge, multi-tenant, security-first backup storage with minimal footprint.

_TLDR; Storage for E2E GPG-encrypted files, with multi-user, quotas, versioning, using a Cloud Storage Provider and deployed on Kubernetes or standalone._

**Natively supports:**
- Kubernetes (but does not require)
- GPG E2E encryption
- Configuration via GitOps (Configuration as a Code)
- Multi-tenancy with configurable Quotas
- Multiple cloud providers as a backend storage (all supported by [GO Cloud](https://gocloud.dev/howto/blob/#services))

**Notice:**
- Project is more focusing on security than on performance
- Due to E2E nature there is no incremental backups support. Incremental backups would need to be implemented client-side with some encrypted metadata stored on server. In the future it may be implemented, but is not our priority. Feel free to send a Pull Request

**Technology stack:**
- Kubernetes Client ([client-go](https://github.com/kubernetes/client-go))
- [GORM for database support](https://gorm.io/index.html)
- [GO Cloud for storage support](https://gocloud.dev/howto/blob)
- [GIN](https://github.com/gin-gonic/gin) + [GIN JWT](https://github.com/appleboy/gin-jwt) for web framework

**Requirements:**
- Kubernetes (if wanting to use Kubernetes)
- PostgreSQL
- About 128Mb ram for small scale usage (**Note**: _We use Argon2di and performing file uploads + calculations on buffers_)
- Storage provider (S3, GCS, Min.io, local filesystem, and others supported by https://gocloud.dev/howto/blob/#services)

Security
--------

- For authentication JSON Web Token was used
- Tokens are long-term due to usage nature
- All JWT's can be revoked anytime. There is a list of generated tokens stored in configuration (only sha256 shortcuts)
- Passwords are encoded with `argon2di` (winner of the 2015 Password Hashing Competition, recommended by OWASP)
- All objects are managed by RBAC (Role Based Access Control) and ACL (Access Control Lists)
- Server works on `uid=1001`, [non-root container](https://kubesec.io/basics/containers-securitycontext-runasnonroot-true/)
- There is a separate [ServiceAccount](https://kubesec.io/basics/service-accounts/) using namespace-scoped roles
- We use [distroless](https://github.com/GoogleContainerTools/distroless) images
- By default, we set [requests and limits](https://kubesec.io/basics/containers-resources-limits-memory/) for `kind: Pod` in Kubernetes

```go
Argon2Config{
    time:    1,
    memory:  64 * 1024,
    threads: 4,
    keyLen:  32,
}
```

### RBAC

Objects of type `kind: BackupUser` (users that can login to Backup Repository server) have a list of global roles.
Global roles are granting access to all objects of given type in the system.

If somebody has a `collectionManager` in its profile, then in all collections that person is a manager which means browsing, deleting, editing, creating.

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupUser
# ...
spec:
    # ...
    roles:
        - collectionManager
```

#### Scoped RBAC

Most of the object types implements `accessControl` to specify permissions for given users in scope of this object.

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
# ...
spec:
    # ...
    accessControl:
        - name: admin
          roles:
              - collectionManager
```

#### RBAC in code

Domain objects should implement a logic that checks given `Actor` if it can act specifically in context of this object.

```go
func (u User) CanViewMyProfile(actor User) bool {
	// rbac
	if actor.Spec.Roles.HasRole(security.RoleUserManager) {
		return true
	}

	// user can view self info
	return u.Spec.Email == actor.Spec.Email
}
```

#### ACL in code

```go
func (c Collection) CanUploadToMe(user *users.User) bool {
	if user.Spec.Roles.HasRole(security.RoleBackupUploader) {
		return true
	}

	for _, permitted := range c.Spec.AccessControl {
		if permitted.UserName == user.Metadata.Name && permitted.Roles.HasRole(security.RoleBackupUploader) {
			return true
		}
	}

	return false
}
```

Quota
-----

`System administrator` can create a collection with specified storage limits on single file, whole collection, select a rotation strategy.

Concept is simple - there can be stored X versions of Y size in given collection. 

Additionally, there is such thing as `extra space` which allows to upload a file that exceeds the limit to not break
the backup pipeline. Such situation is immediately reported in a collection health check as a warning.

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
# ...
spec:
# ...
    maxBackupsCount: 5
    maxOneVersionSize: 1M
    maxCollectionSize: 5M
```

### Extra space

The following example allows uploading files of 1 MB size normally, but optionally allows uploading larger files that could in summary take additional 5MB.
For example one of uploaded versions can be a 5MB file, or there could be two versions of 2,5MB file each - both exceeding the soft limit of `maxOneVersionSize`. The `maxCollectionSize` is a hard limit.

```bash
maxBackupsCount = 5
maxOneVersionSize = 1MB
maxCollectionSize = 10MB

estimatedCollectionSize = maxBackupsCount * maxOneVersionSize = 5 * 1MB = 5MB
extraSpace = maxCollectionSize - estimatedCollectionSize = 10MB - 5MB
```

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
# ...
spec:
# ...
    maxBackupsCount: 5
    maxOneVersionSize: 1M
    maxCollectionSize: 10M
```

### Rotation

Rotation Strategies gives control over backup versioning.

#### `fifo`

First in first out. When adding a new version deletes oldest.

```
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
# ...
spec:
# ...
    strategyName: fifo
```
