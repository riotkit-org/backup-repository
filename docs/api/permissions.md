Permissions
===========

Users can be granted system-level permissions in `kind: BackupUser`, such permissions will apply to all objects in system.
Permissions can be overridden on object-level, for example in `kind: BackupCollection`


| Name              | Description                                         | Scope              |
|-------------------|-----------------------------------------------------|--------------------|
| userManager       | Use user managemet endpoints, lookup any users      | system             |
| collectionManager | Manage collection settings, upload, download        | system, collection |
| backupDownloader  | Upload to a collection                              | system, collection |
| backupUploader    | Download from a collection                          | system, collection |
| uploadsAnytime    | Can upload to collection outside Backup Window time | system, collection |
| systemAdmin       | Unlimited access                                    | system             |


BackupCollection example
------------------------

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupCollection
metadata:
    name: iwa-ait
spec:
    # ...
    accessControl:
        - userName: admin
          roles:
              - collectionManager
```

User example
------------

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupUser
metadata:
    name: iwa-backup-submitter
    namespace: backups
spec:
    # ...
    roles:
        - backupDownloader
        - backupUploader
```
