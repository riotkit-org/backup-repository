@server
Feature: From an administrator perspective I need to know if the application is ready to use, without having the
         knowledge how to use the application. In order to check application health I can invoke a server shell command
         and HTTP endpoint.

    Scenario: As a system administrator I invoke a server shell command, so I can get a health status as a result
        When I check server health from the commandline
        Then I expect the server command contains "storage=True" in output
        And I expect the server command contains "database=True" in output
        And I expect the server command contains "global_status=True" in output


    Scenario: As a system administrator I call HTTP endpoint to get a detailed information about server health
        When I call health endpoint giving "all-cats-are-beautiful-acab" as access code
        Then I should see "storage=True"
        And I should see "database=True"

    Scenario: A short outage of storage backend should show that storage is unavailable
        Given I stop docker container "storage"
        When I call health endpoint giving "all-cats-are-beautiful-acab" as access code
        Then I should see "storage=False"
        And I should see "database=True"

        When I start docker container "storage"
        And I reload the page
        Then I should see "storage=True"

    Scenario: A short outage of database should show that the database is unavailable
        Given I stop docker container "db"
        When I call health endpoint giving "all-cats-are-beautiful-acab" as access code
        And I should see "database=False"

        When I start docker container "db"
        And I reload the page
        Then I should see "database=True"
