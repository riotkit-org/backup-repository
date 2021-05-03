Shell access
============

Backup Repository usage can be automated using shell commands.
Although there is only few commands available, but it should be enough to cover basic automation needs.

It is very helpful to create initially all required accesses and collections, so you can connect applications quickly and start doing backups.

Introduction
------------

*Symfony Console* is accessible in the main directory under **./bin/console**
When using docker you need to get into the container shell to execute it, in our example server environment you need to execute **make sh** to get into the server's shell.

.. code:: shell

    ./bin/console backup:create-collection -d "Some test collection" -f "backup.tar.gz" -b 4 -o 3GB -c 15GB

If something is not working as expected, there is an error and you would like to inspect it, then please add a "-vvv" switch to increase verbosity.

Docker container concept
------------------------

Our container allows to execute commands during startup to help you with the application setup.
With this feature you can create expected tokens, collections on application startup
without need to send any HTTP requests or even log in to the shell. It's an automation you will love.

**Example in docker-compose syntax**

.. code:: yaml

    version: '2.3'
    services:
        filerepository:
            image: quay.io/riotkit/file-repository:${FILE_REPOSITORY_VERSION}
            environment:
                # With this token you can do everything
                SECURITY_ADMIN_TOKEN: "4253f6e5-5c0b-4888-8027-d36bf02eee04"

                # Create a two backup collections, so right after startup you can run a backup, WHY NOT? :-)
                # Please notice, that you can easily use there environment variables
                POST_INSTALL_CMD:
                    ./bin/console backup:create-collection --ignore-error-if-exists --max-backups-count=5
                        --max-one-version-size=10mib --max-collection-size=2gib --strategy=delete_oldest_when_adding_new
                        --filename=postgres.sql.gz --id=3dfa4ea9-1cec-4e24-b773-1cefb9c112c2;

                    ./bin/console backup:create-collection --ignore-error-if-exists --max-backups-count=5
                        --max-one-version-size=5kib --max-collection-size=50kib --strategy=delete_oldest_when_adding_new
                        --filename=postgres-single-db.sql.gz --id=${COLLECTIONS_POSTGRES_SINGLE_DB_ID};
