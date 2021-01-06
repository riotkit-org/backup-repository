Feature: Create users, edit basic fields in existing user profiles, searching the user list

    Background:
        Given I am authenticated as administrator

    # ============
    # Success case
    # ============

    Scenario: Create with success an example user using web panel
        When I visit users search page
        And I press "Add user" button
        And I fill in "Email" with "abcf@abcf.org"
        And I fill in "Organization" with "Anarchist Black Cross"
        And I fill in "New password" with "anarchist-test123456789_"
        And I fill in "Repeat password" with "anarchist-test123456789_"
        And I press "Add new user" button
        Then I should see message "User account saved"

    # ================
    # Basic validation
    # ================

    Scenario: Try to create a user with invalid e-mail address
        When I visit users search page
        And I press "Add user" button
        And I fill in "Email" with "its-not-a-valid-email-address"
        And I fill in "Organization" with "Anarchist Black Cross"
        And I fill in "New password" with "anarchist-test123456789_"
        And I fill in "Repeat password" with "anarchist-test123456789_"
        And I press "Add new user" button
        Then I should not see message "User account saved"
        And I should see message "email: Invalid e-mail format"

    Scenario: Try to create a user, when passwords are not matching
        When I visit users search page
        And I press "Add user" button
        And I fill in "Email" with "its-not-a-valid-email-address"
        And I fill in "New password" with "nestor-makhno-was-a-ukrainian-anarchist"
        And I fill in "Repeat password" with "tsihcrana-nainiarku-a-saw-onhkam-rotsen"
        And I press "Add new user" button
        Then I should see message "Passwords are not matching"
        And I should not see message "User account saved"

    Scenario: Try to create a user with empty password, empty e-mail
        When I visit users search page
        And I press "Add user" button
        And I press "Add new user" button
        Then I should see message "Password is required"
        And I should see message "E-mail is required"
