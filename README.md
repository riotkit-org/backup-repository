Backup Repository
=================

[![Coverage Status](https://coveralls.io/repos/github/riotkit-org/backup-repository/badge.svg?branch=main)](https://coveralls.io/github/riotkit-org/backup-repository?branch=main)
[![Test](https://github.com/riotkit-org/backup-repository/actions/workflows/test.yaml/badge.svg)](https://github.com/riotkit-org/backup-repository/actions/workflows/test.yaml)
[![Artifact Hub](https://img.shields.io/endpoint?url=https://artifacthub.io/badge/repository/riotkit-org)](https://artifacthub.io/packages/search?repo=riotkit-org)

Cloud-native, zero-knowledge, multi-tenant, compliance-strict, security-first backup storage with minimal footprint.

_TLDR; Primitive backup storage for E2E GPG-encrypted files, with multi-user, quotas, versioning, using a object storage (S3/Min.io/GCS etc.) and deployed on Kubernetes or standalone. No fancy stuff included, lightweight and stable as much as possible is the project target._

**Natively supports:**
- Kubernetes (but does not require)
- GPG E2E encryption
- Configuration via GitOps (Configuration as a Code)
- Multi-tenancy with configurable Quotas
- Multiple cloud providers as a backend storage (all supported by [GO Cloud](https://gocloud.dev/howto/blob/#services))
- (Security) JWT tokens with restricted scope (login endpoint can apply additional restrictions to user session)
- (Security) Extra pairs of username & passwords with different restrictions applied - for single user

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
- Storage provider (S3, GCS, Min.io, local filesystem, or others supported by https://gocloud.dev/howto/blob/#services)

**Support:**
- Any Kubernetes 1.20+
- [K3s](https://github.com/k3s-io/k3s)
- OpenShift (with support for Routes, non-privileged, non-root containers)
- PostgreSQL 11+
- [SealedSecrets](https://github.com/bitnami-labs/sealed-secrets)
- [Min.io](https://github.com/minio/minio)

Difference between other backups systems
----------------------------------------

Selecting a best tool depends on specific use case.  Most common way on Kubernetes is to perform cloud-native volume snapshotting, there [Velero project](https://velero.io/docs/v1.8/how-velero-works/) is the most recognized solution that integrates with cloud provides like AWS, Google Cloud or Azure
and uses API calls to ask cloud provider for a snapshot.

**Backup Repository** approach uses **application-native** and more traditional method of performing backups - using tar, pg_dump, mysqldump and other application-native tools for Backup and Restore.
This selected approach have pros and cons as following:

**Pros:**
- Possibility to back up selected part of data e.g. "database X inside PostgreSQL instance"
- Control over data in terms of security: Everything GPG encrypted, storage does not know what is stored. Do you trust your cloud provider at 100%?
- No dependency on public cloud
- Volume driver agnostic, runs even on k3s with local storage provisioner

**Cons:**
- Responsibility for the process to be successful (not just an API call to provider)
- No possibility to do a consistent block device snapshot
- Slower and ineffective at very large scale
- Maintenance and monitoring of scripts that perform backups

Maturity
--------

**Notice:** _This software is currently in a pre-prod stage. We do not plan breaking changes to the functionality, but the API interface may still change. We recommend our official **Backup Maker** client that will be always up-to-date with API changes._

Star a repo, subscribe for releases to get informed.

Security/Compliance demo
------------------------

### Are my backups created in specific time?

Every **Backup Collection** has HTTP health check endpoint you can monitor and trigger alerts in case when expected backup was not submitted or is invalid.

### Attacker got my Kubernetes cluster and wants to overwrite remote backups

- Good practice is to **limit how often versions can be submitted**. Attacker would need to be very patient to overwrite your past backups with malicious ones.

### Attacker got my Backup Repository credentials from target environment

- **Access Keys** feature allows to generate additional pair of username & password for same user, but with fewer privileges
- Use *Backup Maker Operator* which injects JWT credentials on-the-fly just before the backup is made. Those credentials are restricted to upload to single collection at a time
- You may specify ranges of IP addresses from which backup could be submitted (if the server is reachable from the internet)

### Attacker wants to upload a terabyte file to generate cloud costs or exhaust disk space

Backup Repository operates on disk quotas. Every incoming byte stream is calculated on the fly and cancelled, when the limit is exhausted.

### Storage of my Backup Repository server leaked!

End-To-End backup encryption makes your backup unreadable for people not having your GPG private key.

Running
-------

Application is written in GO and distributed as a single-binary file. Recommended way is to run it within a docker image on a Kubernetes cluster.

#### Running standalone

```bash
export AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
export AWS_SECRET_ACCESS_KEY=wJaFuCKtnFEMI/CApItaliSM/bPxRfiCYEXAMPLEKEY

backup-repository \
    --db-password=postgres \
    --db-user=postgres \
    --db-password=postgres \
    --db-name=postgres \
    --jwt-secret-key="secret key" \
    --storage-url="s3://mybucket?endpoint=localhost:9000&disableSSL=true&s3ForcePathStyle=true&region=eu-central-1"
```

#### Installing via Helm

```bash
helm repo add riotkit-org https://riotkit-org.github.io/helm-of-revolution/
helm install backups riotkit-org/backup-repository-server -n backup-repository # --values ...
```

Documentation
-------------

### [For documentation please look into `./docs` directory](./docs/README.md)

**NOTICE:** You are probably reading documentation at `main` branch, please consider selecting a versioned tag from branch/tag selector.

Ecosystem
---------

- [Backup Maker](https://github.com/riotkit-org/br-backup-maker): Uploading & Downloading backups with automated GPG encryption support. CLI client + BMG (Backup Maker procedures Generator) for generating customizable Backup & Restore procedures.
- [RKC](https://github.com/riotkit-org/rkc): Part of `Space Harbor` project, contains CLI integration of `Space Harbor` K8s cluster with Backup Repository
- [PGBR](https://github.com/riotkit-org/br-pgbr): PostgreSQL helpers to be used with `BackupMaker` for reliable backups using native mechanism of dump & restore

Find more projects in the [Github Community](https://github.com/topics/backup-repository).

Security
--------

- For authentication JSON Web Token was used
- Tokens are long-term due to usage nature
- Support for scoped JSON Web Tokens (a single requested token can have restricted permissions to perform less than defined in User profile)
- User can have multiple username & passwords pairs, each one with additional restrictions (e.g. username `mycluster$collection1` -> only uploads to collection1, username `mycluster$collection2` -> only uploads to collection2)
- All JWT's can be revoked anytime. There is a list of generated tokens stored in configuration (only sha256 shortcuts)
- Passwords are encoded with `argon2di` (winner of the 2015 Password Hashing Competition, recommended by OWASP)
- All objects are managed by RBAC (Role Based Access Control) and ACL (Access Control Lists)
- Server works on `uid=65532`, [non-root container](https://kubesec.io/basics/containers-securitycontext-runasnonroot-true/)
- There is a separate [ServiceAccount](https://kubesec.io/basics/service-accounts/) using namespace-scoped roles
- We use [distroless](https://github.com/GoogleContainerTools/distroless) images
- By default, we set [requests and limits](https://kubesec.io/basics/containers-resources-limits-memory/) for `kind: Pod` in Kubernetes
- Built-in simple Request Rate limiter to protect against DoS attacks on application side (Note: The limit is PER application instance. [For more advanced limiting please configure your reverse-proxy properly](http://nginx.org/en/docs/http/ngx_http_limit_req_module.html))
- Each `BackupUser` can be optionally restricted to connect only from allowed IP addresses

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
	if actor.GetRoles().HasRole(security.RoleUserManager) {
		return true
	}

	// user can view self info
	return u.Spec.Email == actor.Spec.Email
}
```

#### ACL in code

```go
func (c Collection) CanUploadToMe(user *users.User) bool {
	if user.GetRoles().HasRole(security.RoleBackupUploader) {
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

#### Backup Windows

Good practice is to **limit how often versions can be submitted**. Attacker would need to be very patient to overwrite your past backups with malicious ones.

In emergency cases `System Administrator` or person with `uploadsAnytime` role can upload backups between backup windows. Be careful! Do not set up automated backups with administrator account or with account that has `uploadsAnytime` role.


```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
# ...
spec:
    # ...
    window:
        # allow to send backups only everyday starting from 00:30 to 01:30
        - from: "00 30 * * *"
          duration: 1h
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

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
# ...
spec:
    # ...
    strategyName: fifo
```

[Contributing](./CONTRIBUTING.md)
---------------------------------

This software is developed with [GoLand](https://www.jetbrains.com/go/) licensed for open source development.
Special thanks for the support.

[Security policy](./SECURITY.md)
--------------------------------
