Configuration reference
=======================

Application configuration
-------------------------

When setting up application without a docker a .env file needs to be created in the root directory of the application.
The .env.dist is a template with example, reference values. If you use a docker image, then you may use those variables as environment variables for the container.

.. literalinclude:: ../../server/.env.dist

..  _permissions_reference:

Permissions list
----------------

You can get a permissions list by accessing an endpoint in your application:

.. code:: json

    GET /auth/roles?_token=test-token-full-permissions

There is also an always up-to-date permissions list, taken directly from the current version of the application for which
the documentation you are browsing.

How to read the list by example:

.. code:: php

    /** Allows to upload images */
    public const ROLE_UPLOAD_IMAGES            = 'upload.images';

Legend:

    - Between /\*\* and \*/ is the description
    - **upload.images** is the role name that you need to know


.. literalinclude:: ../../server/src/Domain/Roles.php
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
  SECURITY_ADMIN_TOKEN=...      (optional) Create admin auth token of given UUIDv4 on container startup
=============================  =====================================================================================


.. _postgresql_support:

PostgreSQL support
------------------


1. **Required extensions to enable in PostgreSQL:**

* uuid-ossp (*CREATE EXTENSION "uuid-ossp";*)

2. **Configuration example:**

**UNIX Socket example:**

.. code:: bash

    DATABASE_URL: ""
    DATABASE_HOST: "/var/run/postgresql"
    DATABASE_NAME: "rojava"
    DATABASE_PASSWORD: "rojava"
    DATABASE_USER: "riotkit"
    DATABASE_DRIVER=pdo_pgsql

    DATABASE_CHARSET=UTF8
    DATABASE_COLLATE=pl_PL.UTF8
    DATABASE_VERSION=10.10

**IPv4 example:**

.. code:: bash

    DATABASE_URL: ""
    DATABASE_HOST: "192.168.2.161"
    DATABASE_NAME: "rojava"
    DATABASE_PASSWORD: "rojava"
    DATABASE_USER: "riotkit"
    DATABASE_DRIVER=pdo_pgsql

    DATABASE_CHARSET=UTF8
    DATABASE_COLLATE=pl_PL.UTF8
    DATABASE_VERSION=10.10


3. **"SQLSTATE[21000]: Cardinality violation: 7 ERROR: more than one row returned by a subquery used as an expression"**

This is an unresolved issue_ in the Doctrine DBAL driver that we use. To work around it, please create
a separate database, user and use default schema "public" for the application.

.. _issue: https://github.com/doctrine/dbal/issues/950
