Collections HTTP & Kubernetes API
=================================

Kubernetes resources
--------------------

### Requirements

#### 1) User

First thing that is required is a **user** ([Learn here how to create one](../users/README.md)), 

#### 2) Created collection

Collection is almost constant, rarely changed, Kubernetes resource that defines a place how backups will be stored.

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
metadata:
    name: iwa-ait
spec:
    description: IWA-AIT website files
    
    # when downloading a file its name will be constructed from this template
    filenameTemplate: iwa-ait-${version}.tar.gz
    
    # how many copies should be stored. Older backups are deleted according to the rotation strategy (see: strategyName attribute)
    maxBackupsCount: 5
    
    # how big could be one version. This is a SOFT limit, if (maxBackupsCount * maxOneVersionSize < maxCollectionSize) then remaining space could be allocated to a version
    # but then the collection's health check will raise alert that extra space was required to store a backup
    maxOneVersionSize: 1M
    
    # hard limit how many space all copies stored in this collection can take
    maxCollectionSize: 10M

    # optional: uploading backups can be only in those time slots. If slot is defined, but there was no backup copy stored in at least one of those slots, then collection's health check will raise an alert
    windows:
        - from: "*/30 * * * *"
          duration: 30m

    # rotation strategies
    # supported values:
    #     fifo: When a new backup copy is uploaded, then oldest is deleted - IF .spec.maxBackupsCount limit was reached.
    strategyName: fifo
    strategySpec: {}

    # backup collection endpoint should be secured with a secret, so only your monitoring software will be able to visit this endpoint
    # the `name` is a `kind: Secret` name, `entry` is a .data.{something} in that `kind: Secret`
    healthSecretRef:
        name: backup-repository-collection-secrets
        entry: iwa-ait

    # optional access control allows defining ranges of access for selected users, this is a kind of ACL
    # if user is not listed there, then user's global permissions are considered. User can be e.g. a global collection manager for all collections
    accessControl:
        - userName: admin
          roles:
              - collectionManager

---
apiVersion: v1
kind: Secret
metadata:
    name: backup-repository-collection-secrets
type: Opaque
data:
    # to generate: use echo -n "admin" | sha256sum
    iwa-ait: "8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918"

```

HTTP API endpoints
------------------

## GET `/api/alpha/repository/collection/:collectionId/version`

Lists currently stored copies of backups in a `BackupCollection` of specified `collectionId`.

**Example response (200):**

```json
{
    "data": {
        "versions": [
            {
                "id": "62dde044-6bdc-48af-b291-1191d8d691cd",
                "collectionId": "iwa-ait",
                "versionNumber": 32,
                "filename": "iwa-ait-32.tar.gz",
                "filesize": 1019254,
                "uploadedBySessionId": "4e1a57bebab9a87faa413934015d9e2ae83cff39cce3b55b02a55e68392964b3",
                "user": "admin",
                "CreatedAt": "2022-02-26T20:29:05.23785+01:00",
                "UpdatedAt": "2022-02-26T20:29:05.23785+01:00",
                "DeletedAt": null
            },
            {
                "id": "13d0c9c9-1889-4258-9dde-b267f3f25d7d",
                "collectionId": "iwa-ait",
                "versionNumber": 33,
                "filename": "iwa-ait-33.tar.gz",
                "filesize": 1019254,
                "uploadedBySessionId": "4e1a57bebab9a87faa413934015d9e2ae83cff39cce3b55b02a55e68392964b3",
                "user": "admin",
                "CreatedAt": "2022-02-26T20:29:19.417054+01:00",
                "UpdatedAt": "2022-02-26T20:29:19.417054+01:00",
                "DeletedAt": null
            }
        ]
    },
  "status": true
}
```

### Response attributes reference

| Attribute           | Description                                                                                                                                                                  |
|---------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| versionNumber       | Version number, increases everytime a backup copy is uploaded. Does not decrease when old backup copies are deleted                                                          |
| collectionId        | Collection identifier allows to interact with collection using HTTP endpoints                                                                                                |
| uploadedBySessionId | Session identifier of a user. Everytime user asks for JWT token there is also a session identifier generated that allows to revoke a session, keep IP address of a requester |
| filesize            | Is a backup copy size in bytes                                                                                                                                               |
| user                | User login who uploaded backup copy                                                                                                                                          |


## `POST /api/alpha/repository/collection/:collectionId/version`

Upload endpoint for a collection. Users with role `backupUploader` can upload files using this endpoint. Role can be assigned in scope of this collection or globally for all collections in the system.

### Supported formats

- HTTP url encoded form (`application/x-www-form-urlencoded` or `multipart/form-data`), the form input name must be `file`
- Raw posted body

**Example response (200):**

```json
{
    "data": {
        "version": {
            "id": "8ee3a487-0229-46fe-a8ce-5817c7f0f5b7",
            "collectionId": "iwa-ait",
            "versionNumber": 1,
            "filename": "iwa-ait-1.tar.gz",
            "filesize": 3057,
            "uploadedBySessionId": "9e39bd799f722a8339a29056b72c9ece57bba49f2b4d3414cd4112526ee30350",
            "user": "admin",
            "CreatedAt": "2022-03-30T08:52:51.691060767+02:00",
            "UpdatedAt": "2022-03-30T08:52:51.691060767+02:00",
            "DeletedAt": null
        }
    },
    "status": true
}
```

## GET `/api/stable/repository/collection/:collectionId/health`

Exposes a health check endpoint for external monitoring software. The purpose of this endpoint is to allow given User to monitor its resources on a shared backups hosting.
Backup Repository assumes that it is multi-tenant and End-To-End encrypted, this leads to natural concept of autonomous monitoring of own resources on almost zero-knowledge storage.

**Query string parameters:**
- `?code=...` (optional, a header `Authorization` can be used instead. e.g. `Authorization my-secret-code-in-plain-text`)

**Headers:**
- `Authorization my-secret-code-here` if `code` not used in query string

### Creating a secret to secure endpoint

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
metadata:
    name: iwa-ait
spec:
# (...)
healthSecretRef:
    name: backup-repository-collection-secrets
    entry: iwa-ait
# (...)

---
apiVersion: v1
kind: Secret
metadata:
    name: backup-repository-collection-secrets
data:
    iwa-ait: "... base64 encoded password ..."
```

**Example response (200):**

```json
{
    "data": {
        "health": [
            {
                "message": "OK",
                "name": "BackupWindowValidator",
                "status": true,
                "statusText": "BackupWindowValidator=true"
            },
            {
                "message": "OK",
                "name": "VersionsSizeValidator",
                "status": true,
                "statusText": "VersionsSizeValidator=true"
            },
            {
                "message": "OK",
                "name": "SumOfVersionsValidator",
                "status": true,
                "statusText": "SumOfVersionsValidator=true"
            }
        ]
    },
    "status": true
}
```

### Response health checks reference

| Check                   | Description                                                                                                                                                                           |
|-------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| BackupWindowValidator   | Activates when at least one Backup Window is defined. Checks if previous backup was made recently in at **least one** Backup Window iteration                                         |
| VersionsSizeValidator   | Fails when at least one collection element exceeds soft limit of single version filesize limit. This should be interpreted as your backup is growing and you should take action soon. |
| SumOfVersionsValidator  | Fails when all stored versions in summary are exceeding hard limit of collection max size                                                                                             |
