Managing collections
====================

To start creating backups you need a collection that will handle ONE FILE.
The file may be a zipped directory, a text file, SQL dump or anything you need.


.. _collection_creation:

Collection creation
--------------------

To add any backup you need a **collection** at first.
Collection is a container that keeps multiple versions of same file (for example your database dump from each day).
Collection additionally can define limits on length, size, type of uploaded file, and tokens which have access to it at all.

Example request:

.. code:: json

    POST {{appUrl}}/repository/collection?_token=test-token-full-permissions

    {
        "maxBackupsCount": 5,
        "maxOneVersionSize": 0,
        "maxCollectionSize": "250MB",
        "strategy": "delete_oldest_when_adding_new",
        "description": "iwa-ait.org database backup",
        "filename": "iwa-ait-org.sql.gz"
    }

In the response you will receive a collection **ID** that will be required for editing collection information, assigning tokens and uploading files.

There are two strategies. **delete_oldest_when_adding_new** is automatically deleting older backup versions
when a ``maxBackupsCount`` is reached and a new backup is submitted. **alert_when_backup_limit_reached** will raise an
error when submitting a new version to already full backup collection.

**Notes:**

- Put zero values to disable the limit
- *Supports "simulate=true" parameter that allows to send a request that will not create any data, but only validate submitted data*
- **You'r token will be automatically added as token allowed to access and modify the collection**


Required permissions:

    - collections.create_new

Optional permissions:

    - collections.allow_infinite_limits (allows to create an infinite collection, it means that you can eg. upload as much files as you like to, and/or the disk space is unlimited)


Collection editing
------------------

.. code:: json

    PUT {{appUrl}}/repository/collection?_token=test-token-full-permissions

    {
        "collection": "SOME-COLLECTION-ID-YOU-RECEIVED-WHEN-CREATING-THE-COLLECTION",
        "maxBackupsCount": 5,
        "maxOneVersionSize": 0,
        "maxCollectionSize": "250MB",
        "strategy": "delete_oldest_when_adding_new",
        "description": "iwa-ait.org database backup (modified)",
        "filename": "iwa-ait-org.sql.gz"
    }

**Notes:**

- The collection size cannot be lower than it is actual in the storage (sum of existing files in the collection)
- You need to have global permissions for managing any collection or to **have token listed as allowed in collection you want to edit**


Required permissions:

    - collections.modify_details_of_allowed_collections

Optional permissions:

    - collections.allow_infinite_limits (allows to edit an infinite collection, it means that you can eg. upload as much files as you like to, and/or the disk space is unlimited)
    - collections.modify_any_collection_regardless_if_token_was_allowed_by_collection (gives a possibility to edit a collection even if token is not attached to it)


Deleting
--------

To delete a collection you need to at first make sure, that there are no backup versions attached to it.
Before deleting a collection you need to manually delete all backups. It's for safety reasons.

.. code:: json

    DELETE {{appUrl}}/repository/collection/SOME-COLLECTION-ID?_token=test-token-full-permissions


Required permissions:

    - collections.delete_allowed_collections

Optional permissions:

    - collections.modify_any_collection_regardless_if_token_was_allowed_by_collection (gives a possibility to edit a collection even if token is not attached to it)


Fetching collection information
-------------------------------

You can fetch information about collection limits, strategy, description and more to be able to edit it using other endpoints.

.. code:: json

    GET {{appUrl}}/repository/collection/SOME-COLLECTION-ID?_token=test-token-full-permissions

**Notes:**

- You need to have global permissions for managing any collection or to **have token listed as allowed in collection you want to fetch**


Required permissions:

    - (just the token added as allowed for given collection)

Optional permissions:

    - collections.modify_any_collection_regardless_if_token_was_allowed_by_collection (gives a possibility to edit a collection even if token is not attached to it)
