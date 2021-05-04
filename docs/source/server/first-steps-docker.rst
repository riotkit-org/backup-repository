Getting started - using docker
##############################

Requirements
------------

- Docker
- docker-compose
- A bit of knowledge how to run docker containers


1. Pick a version
-----------------

Visit https://quay.io/repository/riotkit/backup-repository?tag=latest&tab=tags and pick a Backup Repository version.

**Example**

.. code:: yaml

    quay.io/riotkit/backup-repository:4.0.0


2. Run in compose
-----------------

Create docker-compose.yml file, then run :class:`docker-compose up -d` in the same directory to run application and database.
A good practice is to put passwords in an :class:`.env` file and reference them using :class:`${VAR_NAME}` syntax.

.. code:: yaml

    version: '2.3'
    services:
        app_storage:
            image: quay.io/riotkit/backup-repository:4.0.0
            environment:
                HEALTH_CHECK_CODE: "we-carry-a-new-world-here-in-our-hearts-that-world-is-growing-in-this-minute"

                # persistent storage
                DATABASE_URL: "postgres://myuser:mypassword@pg_database:5432/backup_repository"

                FS_RW_NAME: AWS
                FS_RO_NAME: AWS

                FS_AWS_ADAPTER="aws"
                FS_AWS_ENDPOINT: "http://localhost:9000" # use endpoint to choose between Min.io and AWS
                FS_AWS_BUCKET: "malatesta"
                FS_AWS_REGION: eu-central-1
                FS_AWS_VERSION: latest
                FS_AWS_CREDENTIALS_KEY: "RIOTKIT161ACABEXAMPL"
                FS_AWS_CREDENTIALS_SECRET: "wJalrFUckXEMI/THEdEZG/STaTeandCAPITALKEY"

                APP_ENV: "prod"
                BASE_URL: "https://api.backups.example.org"
                JWT_PASSPHRASE: "no-government-fights-fascism-to-destroy-it"

                # limits
                BACKUP_ONE_VERSION_MAX_SIZE: "25GB"
                BACKUP_COLLECTION_MAX_SIZE: "150GB"
                BACKUP_MAX_VERSIONS: "10"
                PHP_MEMORY_LIMIT: "250M"
                PHP_POST_MAX_SIZE: "25G"

                FPM_PM_MAX_CHILDREN: "4"
                FPM_PM_START_SERVERS: "2"
                FPM_PM_MIN_SPARE_SERVERS: "1"
                FPM_PM_MAX_SPARE_SERVERS: "2"
                FPM_PM_PROCSS_IDLE_TIMEOUT: "10s"
                FPM_PM_MAX_REQUESTS: "100"
                FPM_REQUEST_TERMINATE_TIMEOUT: "3h"

            volumes:
                - /var/run/postgresql/:/var/run/postgresql/
                - ./data/jwt:/home/backuprepository/config/jwt
            ports:
                - "80:80"
            restart: always
            mem_limit: "300M"

        pg_database:
            image: postgres:13.2-alpine
            restart: always
            environment:
                POSTGRES_DB: "backup_repository"
                POSTGRES_PASSWORD: "mypassword"
                POSTGRES_USER: "myuser"
                POSTGRES_ROLE: "myuser"
            volumes:
                - "./data/postgres:/var/lib/postgresql/data/"
            ports:
                - "127.0.0.1:5432:5432"
