@server @security
Feature: Create users, edit basic fields in existing user profiles, searching the user list

    Background:
        Given I am authenticated as administrator

    # ============
    # Success case
    # ============

    Scenario: As an administrator I create with success an example user, then I delete that created profile
        Given I visit users search page
        When I press "Add user" button
        And I fill in "Email" with "anarchist-black-cross@example.org"
        And I fill in "Organization" with "Anarchist Black Cross"
        And I fill in "New password" with "anarchist-test123456789_"
        And I fill in "Repeat password" with "anarchist-test123456789_"
        And I press "Add new user" button
        Then I should see message "User account saved"

        When I visit users search page
        And I follow "anarchist-black-cross@example.org"
        And I prepare to confirm the prompt with "anarchist-black-cross@example.org"
        And I press "Delete User" button
        Then I should see message "User account deleted"

    # ==================================
    # Basic validation in USERS CREATION
    # ==================================

    Scenario: As an administrator I try to create a user with invalid e-mail address
        When I visit users search page
        And I press "Add user" button
        And I fill in "Email" with "its-not-a-valid-email-address"
        And I fill in "Organization" with "Anarchist Black Cross"
        And I fill in "New password" with "anarchist-test123456789_"
        And I fill in "Repeat password" with "anarchist-test123456789_"
        And I press "Add new user" button
        Then I should not see message "User account saved"
        And I should see message "email: Invalid e-mail format"


    Scenario: As an administrator I try to create a user, when passwords are not matching
        When I visit users search page
        And I press "Add user" button
        And I fill in "Email" with "its-not-a-valid-email-address"
        And I fill in "New password" with "nestor-makhno-was-a-ukrainian-anarchist"
        And I fill in "Repeat password" with "tsihcrana-nainiarku-a-saw-onhkam-rotsen"
        And I press "Add new user" button
        Then I should see message "Passwords are not matching"
        And I should not see message "User account saved"


    Scenario: As an administrator I try to create a user with empty password, empty e-mail
        When I visit users search page
        And I press "Add user" button
        And I press "Add new user" button
        Then I should see message "Password is required"
        And I should see message "E-mail is required"

    # ===========================================================
    # Adding roles to user account and logging in as that account
    # ===========================================================

    Scenario: As an administrator I create an account without permissions, then I edit account and make it an "administrator"
        # I. Create
        Given I visit users search page
        When I press "Add user" button
        And I fill in "Email" with "anarchist-black-cross@example.org"
        And I fill in "Organization" with "Anarchist Black Cross"
        And I fill in "New password" with "anarchist-test123456789_"
        And I fill in "Repeat password" with "anarchist-test123456789_"
        And I press "Add new user" button
        Then I should see message "User account saved"

        # II. Edit
        Given I visit users search page
        When I follow "anarchist-black-cross@example.org"
        And I follow "Toggle permissions names/descriptions"
        And I check "security.administrator"
        And I press "Update Profile" button
        Then I should see message "User account saved"

        # III. Verify by logging in
        Given I logout
        And I login as "anarchist-black-cross@example.org" with "anarchist-test123456789_"
        When I visit users search page
        Then I should see user "anarchist-black-cross@example.org" on the list
        And I expect that the footer containing application version is visible


    Scenario: As an administrator I create an account with limited roles and I will attempt to display users list
        # I. Create a user that is able to only upload backups, nothing else
        Given I visit users search page
        When I press "Add user" button
        And I fill in "Email" with "anarchist-black-cross@example.org"
        And I fill in "Organization" with "Anarchist Black Cross"
        And I fill in "New password" with "anarchist-test123456789_"
        And I fill in "Repeat password" with "anarchist-test123456789_"
        And I check "collections.upload_to_allowed_collections"
        And I check "collections.list_versions_for_allowed_collections"
        And I check "collections.fetch_single_version_file_in_allowed_collections"
        And I press "Add new user" button
        Then I should see message "User account saved"

        # II. Verify by logging in
        Given I logout
        And I login as "anarchist-black-cross@example.org" with "anarchist-test123456789_"
        When I visit users search page
        Then I should see message "No permission to search for users"

        # III. Edit user - grant him access to display users list
        Given I logout
        And I am authenticated as administrator
        And I visit users search page
        And I follow "anarchist-black-cross@example.org"
        And I follow "Toggle permissions names/descriptions"
        And I check "security.search_for_users"
        And I check "security.authentication_lookup"
        And I press "Update Profile" button
        Then I should see message "User account saved"

        # IV. Verify that additional permissions were granted on the limited permissions user
        Given I logout
        And I login as "anarchist-black-cross@example.org" with "anarchist-test123456789_"
        When I visit users search page
        Then I should not see message "No permission to search for users"


    Scenario: As an administration I create account that has short expiration time. When the account expires
              then the user can no longer log in

        # I. Create a test user, that already has expired account
        Given I visit users search page
        When I press "Add user" button
        And I fill in "Email" with "expired-user@example.org"
        And I fill in "Organization" with "Moscow Death Brigade"
        And I fill in "New password" with "anarchist-test123456789_"
        And I fill in "Repeat password" with "anarchist-test123456789_"
        And I pick date "{yesterday}" from "Optionally set expiration date"
        And I press "Add new user" button
        Then I should see message "User account saved"

        # II. Try to login as this user
        Given I logout
        And I login as "expired-user@example.org" with "anarchist-test123456789_"
        Then I should see message "User account is no longer active"

        # III. Make the user account active
        Given I am authenticated as administrator
        And I visit users search page
        When I follow "expired-user@example.org"
        And I pick date "{tommorow}" from "Optionally set expiration date"
        And I press "Update Profile" button
        Then I should see message "User account saved"

        # IV. Test that user account is active
        Given I logout
        And I login as "expired-user@example.org" with "anarchist-test123456789_"
        Then I should not see message "User account is no longer active"
