Getting started - classic way
#############################

Requirements
------------

- PHP 8
- PHP-FPM
- Reverse Proxy (NGINX, Apache 2 or other. We recommend NGINX)
- PostgreSQL 11+
- One of storages: Google Cloud Storage, AWS S3, Min.io. If you don't want to use any of them, don't worry - you can still use a local disk storage provider

**Notice:** *Application handles file uploads - it may be problematic to use a shared webhosting, AWS lambdas, Google Cloud Applications and other services that have hard limits on request size and duration*

Manual installation
*******************

1. Configure reverse proxy (NGINX, Apache 2 or other)
-----------------------------------------------------

Use following configuration snippet as a reference.

**Notices:**

- Application cannot be installed in a subdirectory, must be at root path of a domain ("/") ex. :class:`https://backups.example.org/`
- :class:`^/api/stable/repository/collection/([A-Za-z0-9\-]+)/versions$` endpoint should be allowed to take long and big requests, it is a path where files are uploaded
- If you have multiple layers of reverse proxy you may want to adjust :class:`proxy_read_timeout`, :class:`proxy_send_timeout` and consider turning off :class:`proxy_buffering` and :class:`proxy_request_buffering`


.. literalinclude:: ../_static/examples/nginx.conf
   :language: nginx

2. Create a user and a database in PostgreSQL
---------------------------------------------

Best practice is to have a separate user for each application, we recommend you to create a separate user with non-trivial password.

.. code:: bash

    # optionally jump into a postgres user
    su postgres

    # you may be asked for a password (it depends on how is your access configured in pg_hba.conf)
    psql


**Hints:**

- Use a difficult, long, generated database password for security. You don't have to remember that password, it will be in a configuration file
- Use a non-standard username and database name, so the potential attacker could even not guess that

.. code:: postgresql

    CREATE USER backup_repository WITH PASSWORD 'psssst_put_your_secret_there';
    CREATE DATABASE backup_repository_db;
    GRANT ALL PRIVILEGES ON DATABASE backup_repository_db TO backup_repository;


Now think about how the application should be connecting to database - using TCP/IP or Unix socket, you may want to check of the pg_hba.conf documentation at https://www.postgresql.org/docs/current/auth-pg-hba-conf.html

3. Unpack Backup Repository server to target directory ex. /var/www - download a fresh distribution from releases tab on Github
-------------------------------------------------------------------------------------------------------------------------------

4. Configure application
------------------------

.. code:: bash

    cd /var/www

    # create configuration from template
    cp .env.dist .env

    edit .env

    # 1) Put your database credentials in line: DATABASE_URL=postgres://chomsky:chomsky@127.0.0.1:5432/chomsky
    # 2) Generate a random string and replace passphrase in line: JWT_PASSPHRASE=fc0774955def1f2e92e6bdcad18a9f97
    # 3) Change a health check code in line: HEALTH_CHECK_CODE=test

    # Configure a storage adapter
    # 1) Pick AWS, GC (Google Cloud) or LOCAL configuration
    # 2) Modify selected configuration to your needs
    # 3) Put configuration name in FS_RW_NAME and in FS_RO_NAME

5. Generate JWT keys
--------------------

Generate private keys, replace "$JWT_PASSPHRASE" with a passphrase you have in :class:`.env` file. Keep the files in secret.

.. code:: bash

    openssl genpkey -out config/jwt/private.pem -aes256 -pass pass:$JWT_PASSPHRASE -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:$JWT_PASSPHRASE


6. Install dependencies
-----------------------

.. code:: bash

    cd /var/www
    composer install

7. Create an administrator account
----------------------------------

.. code:: bash

    cd /var/www
    ./bin/console auth:create-admin-account --email example@riseup.net --password example_1234

8. DONE! Navigate to http://localhost and login using your created administrator account
----------------------------------------------------------------------------------------
