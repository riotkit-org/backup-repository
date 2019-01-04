Backups: Upload, deletion and versioning
========================================

Assuming that you have already a collection and an access token, then we can start
uploading files that will be versioned and stored under selected collection.

Uploading a new version to the collection
-----------------------------------------

You need to submit **file content in the HTTP request body**.
The rest of the parameters such as token you need to pass as GET parameters.

.. code:: json

    POST /repository/collection/{{collection_id}}/backup?_token={{token_that_allows_to_upload_to_allowed_collections}}

    .... FILE CONTENT THERE ....

Pretty simple, huh?
As the result you will get the version number and the filename, something like this:

.. code:: json

    {
        "status": "OK",
        "error_code": null,
        "exit_code": 200,
        "field": null,
        "errors": null,
        "version": {
            "id": "69283AC3-559C-43FE-BFCC-ECB932BD57ED",
            "version": 1,
            "creation_date": {
                "date": "2019-01-03 11:40:14.669724",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "file": {
                "id": 175,
                "filename": "ef61338f0dsolidarity-with-postal-workers-article-v1"
            }
        },
        "collection": {
            "id": "430F66C3-E4D9-46AA-9E58-D97B2788BEF7",
            "max_backups_count": 2,
            "max_one_backup_version_size": 1000000,
            "max_collection_size": 5000000,
            "created_at": {
                "date": "2019-01-03 11:40:11.000000",
                "timezone_type": 3,
                "timezone": "UTC"
            },
            "strategy": "delete_oldest_when_adding_new",
            "description": "Title: Solidarity with Postal Workers, Against State Repression!",
            "filename": "solidarity-with-postal-workers-article"
        }
    }

Required permissions:

    - collections.upload_to_allowed_collections


Deleting a version
------------------

A simple DELETE type request will delete a version from collection and from storage.

.. code:: json

    DELETE /repository/collection/{{collection_id}}/backup/BACKUP-ID?_token={{token}}

Example response:

.. code:: json

    {
        "status": "OK, object deleted",
        "error_code": 200,
        "exit_code": 200
    }


======  ========= ====================================================================================
   Parameters
----------------- ------------------------------------------------------------------------------------
 type    name      description
======  ========= ====================================================================================
bool    simulate   Simulate the request, do not delete in real. Could be used as pre-validation
string  _token     Standard access token parameter (optional, header can be used instead)
======  ========= ====================================================================================


Required permissions:

    - collections.delete_versions_for_allowed_collections

Getting the list of uploaded versions
-------------------------------------

To list all existing backups under a collection you need just a collection id, and the permissions.

.. code:: json

    GET /repository/collection/{{collection_id}}/backup?_token={{token}}

Example response:

.. code:: json

    {
        "status": "OK",
        "error_code": null,
        "exit_code": 200,
        "versions": {
            "3": {
                "details": {
                    "id": "A9DAB651-3A6F-440D-8C6D-477F1F796F13",
                    "version": 3,
                    "creation_date": {
                        "date": "2019-01-03 11:40:24.000000",
                        "timezone_type": 3,
                        "timezone": "UTC"
                    },
                    "file": {
                        "id": 178,
                        "filename": "343b39f56csolidarity-with-postal-workers-article-v3"
                    }
                },
                "url": "https://my-anarchist-initiative/public/download/343b39f56csolidarity-with-postal-workers-article-v3"
            },
            "4": {
                "details": {
                    "id": "95F12DAD-3F03-49B0-BAEA-C5AC3E8E2A30",
                    "version": 4,
                    "creation_date": {
                        "date": "2019-01-03 11:47:34.000000",
                        "timezone_type": 3,
                        "timezone": "UTC"
                    },
                    "file": {
                        "id": 179,
                        "filename": "41ea3dcca9solidarity-with-postal-workers-article-v4"
                    }
                },
                "url": "https://my-anarchist-initiative/public/download/41ea3dcca9solidarity-with-postal-workers-article-v4"
            }
        }
    }


Required permissions:

    - collections.list_versions_for_allowed_collections


Downloading uploaded versions
-----------------------------

Given we upload eg. 53 versions of a SQL dump, one each month and we want to download latest version, then
we need to call the fetch endpoint with the **"latest"** keyword as the identifier.

.. code:: json

    GET /repository/collection/{{collection_id}}/backup/latest?password={{collection_password_to_access_file}}&_token={{token}}

If there is a need to download an older version of the file, a **version number should be used, eg. v49**

.. code:: json

    GET /repository/collection/{{collection_id}}/backup/v49?password={{collection_password_to_access_file}}&_token={{token}}

There is also a possibility to download a last copy from the bottom, the oldest version available using keyword **first**.

.. code:: json

    GET /repository/collection/{{collection_id}}/backup/first?password={{collection_password_to_access_file}}&_token={{token}}

In case we have an **ID of the version**, then it could be inserted directly replacing the alias keyword.

.. code:: json

    GET /repository/collection/{{collection_id}}/backup/69283AC3-559C-43FE-BFCC-ECB932BD57ED?password=thats-a-secret&_token={{token}}



======  ========= ====================================================================================
   Parameters
----------------- ------------------------------------------------------------------------------------
 type    name      description
======  ========= ====================================================================================
bool    redirect   Allows to disable HTTP redirection and return JSON with the url address instead
string  password   Password required for requested FILE (please read about passwords in notes section)
string  _token     Standard access token parameter (optional, header can be used instead)
======  ========= ====================================================================================



Required permissions:

    - collections.list_versions_for_allowed_collections
    - (knowing the password for the collection file)


Notes:

    - *The password for the file is inherited from collection, but it may be different in case when the collection would have changed the password, old files would not be updated!*



