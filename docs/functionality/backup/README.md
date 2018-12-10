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
    "description": "Test collection (modified)"
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
