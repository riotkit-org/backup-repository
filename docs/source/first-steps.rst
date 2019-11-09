First steps
===========

To start using the application you need to install PHP 7.3 with extensions listed in `composer.json` file (see entries ext-{name}),
composer.

You can also use a ready-to-use docker container instead of using host installation of PHP, **if you have a possibility always use a docker container**.

Summary of application requirements:

- PHP 7.3 or newer
- SQLite3, MySQL 5.7+ or PostgreSQL 10+
- Composer (PHP package manager, see packagist.org)
- make (GNU Make)

Notice: For PostgreSQL configuration please check the configuration reference at :ref:`postgresql_support` page

Manual installation
===================

At first you need to create your own customized `.env` file with application configuration.
You can create it from a template `.env.dist`.

Make sure the **APP_ENV** is set to **prod**, and that the database settings are correct.
On default settings the application should be connecting to a SQLite3 database placed in local file, but this is
not optimal for production usage.

.. code:: shell

    cp .env.dist .env
    edit .env

To install the application - download dependencies, install database schema use the make task **install**.

.. code:: bash

    make install

All right! The application should be ready to go. To check the application you can launch a **development web server**.

.. code:: bash

    make run_dev



Installation with docker
========================

You have at least three choices:

- Use `quay.io/riotkit/file-repository` container by your own (advanced)
- Use a prepared docker-compose environment placed in `examples/docker` directory
- Create your own environment basing on provided example docker-compose

Proposed way to choose is the prepared docker-compose environment that is placed in `examples/docker` directory.
Here are instructions how to start with it:

.. code:: bash

    # go to the environment directory and copy template file
    cd ./examples/docker-s3-api-client
    cp .env.dist .env

    # adjust application settings
    edit .env


Now adjust the environment variables to your need - you might want to see the configuration reference.
If you think the configuration is finished, start the environment. To stop it - type CTRL+C.

.. code:: bash

    # start the environment
    make start


Example docker-compose.yml file:

.. literalinclude:: ../../examples/docker-s3-api-client/docker-compose.yml
   :language: yaml
   :linenos:


Post-installation
=================

At this point you have the application, but you do not have access to it.
**You will need to generate an administrative access token** to be able to create new tokens, manage backups, upload files to storage.
To achieve this goal you need to execute a simple command.

Given you use docker you can do eg. **sudo docker exec some-container-name ./bin/console auth:generate-admin-token**,
for bare metal installation it would be just **./bin/console auth:generate-admin-token** in the project directory.

So, when you have an administrative token, then you need a token to upload backups. It's not recommended to use administrative token
on your servers. **Recommended way is to generate a separate token, that is allowed to upload a backup to specified collection**

To do so, check all available roles in the application:

.. code:: bash

    GET /auth/roles?_token=YOUR-ADMIN-TOKEN-HERE

:ref:`Note: If you DO NOT KNOW HOW to perform a request, then please check the postman section <postman>`

You should see something like this:

.. code:: json

    {
        "roles": {
            "upload.images": "Allows to upload images",
            "upload.documents": "Allows to upload documents",
            "upload.backup": "Allows to submit backups",
            "upload.all": "Allows to upload ALL types of files regardless of mime type",
            "security.authentication_lookup": "User can check information about ANY token",
            "security.overwrite": "User can overwrite files",
            "security.generate_tokens": "User can generate tokens with ANY roles",
            "security.use_technical_endpoints": "User can use technical endpoints to manage the application",
            "deletion.all_files_including_protected_and_unprotected": "Delete files that do not have a password, and password protected without a password",
            "view.any_file": "Allows to download ANY file, even if a file is password protected",
            "view.files_from_all_tags": "List files from ANY tag that was requested, else the user can list only files by tags allowed in token",
            "view.can_use_listing_endpoint_at_all": "Define that the user can use the listing endpoint (basic usage)",
            "collections.create_new": "Allow person creating a new backup collection",
            "collections.allow_infinite_limits": "Allow creating backup collections that have no limits on size and length",
            "collections.modify_any_collection_regardless_if_token_was_allowed_by_collection": "Allow to modify ALL collections. Collection don't have to allow such token which has this role",
            "collections.view_all_collections": "Allow to browse any collection regardless of if the user token was allowed by it or not",
            "collections.can_use_listing_endpoint": "Can use an endpoint that will allow to browse and search collections?",
            "collections.manage_tokens_in_allowed_collections": "Manage tokens in the collections where our current token is already added as allowed",
            "collections.upload_to_allowed_collections": "Upload to allowed collections",
            "collections.list_versions_for_allowed_collections": "List versions for collections where the token was added as allowed",
            "collections.delete_versions_for_allowed_collections": "Delete versions only from collections where the token was added as allowed"
        }
    }

To allow only uploading and browsing versions for assigned collections you may choose:

.. code:: bash

    POST /auth/token/generate?_token=YOUR-ADMIN-TOKEN-THERE
    {
        "roles": ["upload.backup", "collections.upload_to_allowed_collections", "collections.list_versions_for_allowed_collections"],
        "data": {
            "tags": [],
            "allowedMimeTypes": [],
            "maxAllowedFileSize": 0
        }
    }

As the response you should get the token id that you need.

.. code:: json

    {
        "tokenId": "34A77B0D-8E6F-40EF-8E70-C73A3F2B3AF8",
        "expires": null
    }

**Remember the tokenId**, now you can create collections and grant access for this token to your collections.
Generated token will be able to upload to collections you allow it to.

Check next steps:

1. :ref:`collection_creation`
2. :ref:`granting_access_to_collection`

That's all.
