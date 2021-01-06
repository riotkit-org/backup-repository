Feature: I can create an administrator account using server's shell interface,
    so I could gain access to the instance and start preparing it to my needs.
    The shell command should also implement validation, just same like in web API interface.


    Scenario: I try to create an administrator account with e-mail "unity@solidarity.local"
        Given I create admin account with e-mail "unity@solidarity.local" and "you-cant-break-it" password
        When I login as "unity@solidarity.local" with "you-cant-break-it"
        Then I expect to be logged in
        And I expect that the footer containing application version is visible


    Scenario: I should be warned that I cannot create an account with invalid e-mail format
        Given I create admin account from shell command with "--email='test' --password='brother_and_sister_hood_161'" advanced options
        Then I expect the server command contains "Invalid e-mail format (code: 40005)" in output


    Scenario: I should be notified that the user already exists, if I do not use switch "--ignore-error-if-already-exists"
        Given I create admin account from shell command with "--email=unity@solidarity.local --password=you-cant-break-it" advanced options
        Then I expect the server command contains "User already exists (code: 40001)" in output


    Scenario: The "User already exists (code: 40001)" error should be not present, and user creation should pass if I use switch "--ignore-error-if-already-exists"
        Given I create admin account from shell command with "--email=unity@solidarity.local --password=you-cant-break-it --ignore-error-if-already-exists" advanced options
        Then I expect the server command exited with 0 exit code
