Getting started
===============

The workflow is following:

    1. You need to have an access token that allows you to create collections
    2. **Create a collection**, remember it's ID (we will call it **collection_id** later)
    3. (Optional) Allow some other token or tokens to access the collection (all actions or only some selected actions on the collection)
    4. **Store backups** under a collection of given **collection_id**
    5. List and download stored backups when you need

Versioning
----------

Each uploaded version is added as last and have a version number incremented by one, and a **ID** string generated.

For example:
    There is a **v1** version, **we upload a new version and a new version is getting a number v2**

Later any version could be accessed by generated **ID** string or **version number** (in combination with the **collection ID**)

Collection limits
-----------------

Each collection could either be a infinite collection or a finite collection.

Below are listed limits for finite collections:

===================  ====================================================================================
   Limits
-------------------  ------------------------------------------------------------------------------------
 limit                description
===================  ====================================================================================
  maxBackupsCount     Maximum count of versions that could be stored
  maxOneVersionSize   Maximum disk space that could be allocated for single version
  maxCollectionSize   Maximum disk space for whole collection (summary of all files)
===================  ====================================================================================


Permissions
-----------

There could be multiple tokens with different permissions assigned to the collection.

Example use case:
    Generated **"Guest token"** with download-only permissions could be safe to share between administrators.
    The **"Upload token"** could be used by the server to automatically upload new versions without permissions
    to delete other versions and without need to modify collections limits.
    **"Management token"** with all of the permissions for managing a collection.
