Backup
======

Backups allows to store multiple versions of same file.

Endpoints
---------

##### Collection creation

To add any backup you need a **collection** at first.
Collection is a container that keeps multiple versions of same file (for example your database dump from each day).
Collection additionally can define limits on length, size, type of uploaded file, and tokens which have access to it at all.

Example request:
```
POST {{appUrl}}/repository/collection?_token=test-token-full-permissions

{
    "maxBackupsCount": 5,
    "maxOneVersionSize": 0,
    "maxCollectionSize": "250MB",
    "strategy": "delete_oldest_when_adding_new",
    "description": "Test collection"
}
```

There are two strategies. **delete_oldest_when_adding_new** is automatically deleting older backup versions
when a `maxBackupsCount` is reached and a new backup is submitted. **alert_when_backup_limit_reached** will raise an
error when submitting a new version to already full backup collection.

**Note:** Put zero values to disable the limit
**Note:** _Supports "simulate=true" parameter that allows to send a request that will not create any data, but only validate submitted data_
