Configuration reference
=======================

Application configuration
-------------------------

When setting up application without a docker a .env file needs to be created in the root directory of the application.
The .env.dist is a template with example, reference values. If you use a docker image, then you may use those variables as environment variables for the container.

.. literalinclude:: ../../.env.dist

..  _permissions_reference:

Permissions list
----------------

You can get a permissions list by accessing an endpoint in your application:

.. code:: json

    GET /auth/roles?_token=test-token-full-permissions

There is also an always up-to-date permissions list, taken directly from the recent version of the application.

How to read the list by example:

.. code:: php

    /** Allows to upload images */
    public const ROLE_UPLOAD_IMAGES            = 'upload.images';

Legend:

    - Between /\*\* and \*/ is the description
    - **upload.images** is the role name


.. literalinclude:: ../../src/Domain/Roles.php
   :language: ruby

Docker container extra parameters
---------------------------------

Parameters passed to docker container are mostly application configuration parameters, but not only.
There exists extra parameters that are implemented by the docker container itself, they are listed below:

=============================  =====================================================================================
-----------------------------  -------------------------------------------------------------------------------------
 Name and example               Description
=============================  =====================================================================================
  WAIT_FOR_HOST=db_mysql:3306   (optional) Waits up to 2 minutes for host to be up when starting a container
  SENTRY_DSN=url-here           (optional) Enables integration with sentry.io, so all failures will be logged there
=============================  =====================================================================================


.. _postgresql_support:

PostgreSQL support
------------------

1. Required extensions:
- uuid-ossp (*CREATE EXTENSION "uuid-ossp";*)

2. Due to lack of Unix sockets support in Doctrine Dbal library we created a custom PostgreSQL adapter.

**UNIX Socket example:**

.. code:: bash

    DATABASE_URL=
    POSTGRES_DB_PDO_ROLE=... (in most cases same as username)
    POSTGRES_DB_PDO_DSN="pgsql:host=/var/run/postgresql;user=...;dbname=...;password=...;"
    DATABASE_CHARSET=UTF8
    DATABASE_COLLATE=pl_PL.UTF8
    DATABASE_DRIVER=pdo_pgsql
    DATABASE_VERSION=10.10

**IPv4 example:**

.. code:: bash

    DATABASE_URL=
    POSTGRES_DB_PDO_ROLE=... (in most cases same as username)
    POSTGRES_DB_PDO_DSN="pgsql:host=my_db_host;user=...;dbname=...;password=...;"
    DATABASE_CHARSET=UTF8
    DATABASE_COLLATE=pl_PL.UTF8
    DATABASE_DRIVER=pdo_pgsql
    DATABASE_VERSION=10.10
