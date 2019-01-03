Backup
======

Backup collections allows to store multiple versions of the same file.
Each submitted version has automatically incremented version number by one.

Example scenario with strategy "delete_oldest_when_adding_new":
```
Given we have DATABASE dumps of iwa-ait.org website
And our backup collection can contain only 3 versions (maximum)

When we upload a sql dump file THEN IT'S a v1 version
When we upload a next sql dump file THEN IT'S a v2 version
When we upload a next sql dump file THEN IT'S a v3 version

Then we have v1, v2, v3

When we upload a sql dump file THEN IT'S a v4 version
But v1 gets deleted because collection is full

Then we have v2, v3, v4
```

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
    "description": "iwa-ait.org database backup",
    "filename": "iwa-ait-org.sql.gz"
}
```

There are two strategies. **delete_oldest_when_adding_new** is automatically deleting older backup versions
when a `maxBackupsCount` is reached and a new backup is submitted. **alert_when_backup_limit_reached** will raise an
error when submitting a new version to already full backup collection.

**Notes:** 
- Put zero values to disable the limit
- _Supports "simulate=true" parameter that allows to send a request that will not create any data, but only validate submitted data_
- **You'r token will be automatically added as token allowed to access and modify the collection**

##### Collection editing

```
PUT {{appUrl}}/repository/collection?_token=test-token-full-permissions

{
    "collection": "SOME-COLLECTION-ID-YOUR-RECEIVED-WHEN-CREATING-THE-COLLECTION",
    "maxBackupsCount": 5,
    "maxOneVersionSize": 0,
    "maxCollectionSize": "250MB",
    "strategy": "delete_oldest_when_adding_new",
    "description": "iwa-ait.org database backup (modified)",
    "filename": "iwa-ait-org.sql.gz"
}
```

**Notes:**
- The collection size cannot be lower than it is actual in the storage (sum of existing files in the collection)
- You need to have global permissions for managing any collection or to **have token listed as allowed in collection you want to edit**

##### Deleting

To delete a collection you need to at first make sure, that there are no backup versions attached to it.
Before deleting a collection you need to manually delete all backups. It's for safety reasons.

```
DELETE {{appUrl}}/repository/collection/SOME-COLLECTION-ID-YOUR-RECEIVED-WHEN-CREATING-THE-COLLECTION?_token=test-token-full-permissions
```

##### Fetching collection information

You can fetch information about collection limits, strategy, description and more to be able to edit it using other endpoints.

```
GET {{appUrl}}/repository/collection/SOME-COLLECTION-ID-YOUR-RECEIVED-WHEN-CREATING-THE-COLLECTION?_token=test-token-full-permissions
```

**Notes:**
- You need to have global permissions for managing any collection or to **have token listed as allowed in collection you want to fetch**
