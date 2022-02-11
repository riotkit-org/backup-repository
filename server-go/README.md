Backup Repository
=================

Cloud-native, zero-knowledge, multi-tenant backup storage with minimal footprint.

**Natively supports:**
- Kubernetes (but does not require)
- GPG E2E encryption
- Configuration via GitOps (Configuration as a Code)


Security
--------

- For authentication JSON Web Token was used
- Tokens are long-term due to usage nature
- All JWT's can be revoked anytime. There is a list of generated tokens stored in configuration (only sha256 shortcuts)
- Passwords are encoded with `argon2di` (winner of the 2015 Password Hashing Competition, recommended by OWASP)
- All objects are managed by RBAC (Role Based Access Control)

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
	if actor.Spec.Roles.HasRole(security.RoleSysAdmin) || actor.Spec.Roles.HasRole(security.RoleUserManager) {
		return true
	}

	// user can view self info
	return u.Spec.Email == actor.Spec.Email
}
```
