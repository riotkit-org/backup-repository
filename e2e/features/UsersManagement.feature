Feature: Create users, edit basic fields in existing user profiles, searching the user list

    Background:
        Given I am authenticated as administrator

    Scenario: Create a example user from web panel
        When I visit users search page
        And I press "Add user" button
        And I fill in "Email" with "abcf@abcf.org"
        And I fill in "Organization" with "Anarchist Black Cross"
        And I fill in "New password" with "anarchist-test123456789_"
        And I fill in "Repeat password" with "anarchist-test123456789_"
        And I press "Add new user" button
        Then I should see message "User account saved"
