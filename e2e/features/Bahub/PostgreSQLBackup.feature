@bahub @backup @server

Feature: As a system administrator I need to be able to create automated backups of a PostgreSQL database
         as a whole instance as well as single database

    Background:
        Given I am authenticated as administrator
        And I visit backups page
        And I create a backup with filename="international-workers-association.tar.gz" description="IWA-AIT.org backup" strategy="FIFO - delete oldest on adding new" maxBackupsCount=2 maxOneVersionSize=50MB maxOverallCollectionSize=110MB
        And I generate a new access key with all permissions


    Scenario: As a system administrator I can make a backup of a whole instance (with all databases and users)
        When I visit recently created collection page
        And I generate keys for existing backup configuration entry "db_postgres_dump_all_databases"
        And I submit a new backup as part of "db_postgres_dump_all_databases" definition for collection I recently created
        And I reload the page
        Then I expect that there are "v1" backups present

        # Test database state before restore
        When I import SQL file "testfiles/Bahub/postgresql_tables.sql" into PostgreSQL test instance connecting to postgres database
        Then I execute SQL "SELECT number FROM numbers" on PostgreSQL test instance in postgres database expecting "161" result

        # Test database after restore
        When I issue a backup restore of "latest" version using "db_postgres_dump_all_databases" definition for a collection I recently created
        Then I execute SQL "SELECT number FROM numbers" on PostgreSQL test instance in postgres database expecting 'relation "numbers" does not exist' result


    Scenario: As a system administrator I can make a backup of a single database inside of a PostgreSQL instance
        When I visit recently created collection page
        And I generate keys for existing backup configuration entry "db_postgres_dump_all_databases"
        And I submit a new backup as part of "db_postgres_dump_all_databases" definition for collection I recently created
        And I reload the page
        Then I expect that there are "v1" backups present
