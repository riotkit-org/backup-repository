@server @security
Feature: I can manage my own access list

    Background:
        Given I am authenticated as administrator

    Scenario: I can revoke current session, so then I expect that I will be logged out
        Given I visit authorization page
        And I follow "Revoke"
        Then I should see message "No active token found"


    Scenario: As a user with all permissions I can generate a new API token for longer period than 1 hour,
              then I can REVOKE that token

        Given I visit authorization page
        And I follow "Grant a new access"
        And I select "+7 days" from "Select how long the token should be valid"
        And I fill in "description" with "Test token"
        And I follow "Create access token"
        Then I should see "Access token generated"

        When I close the popped modal
        And I revoke the token "Test token"
        Then I should see message "Access revoked"
        And I expect the Revoke button will be disabled for "Test token"


    Scenario: As an administrator I want to generate an API access token with LIMITED PERMISSIONS, so I could later
              use that token securely in one of my client applications.

        Given I visit authorization page
        When I follow "Grant a new access"
        And I select "+7 days" from "Select how long the token should be valid"
        And I follow "collections.can_use_listing_endpoint"
        And I follow "collections.view_all_collections"
        And I fill in "description" with "Test token"
        And I follow "Create access token"
        And I copy the authorization token
        Then I close the popped modal

        Given I logout
        When I login using copied JWT
        And I visit users search page
        Then I should see message "No permission to search for users"
        When I visit collections page
        Then I should not see message "No permissions to list collections"


    Scenario: As an administrator I want need to create an API token with all my permissions, for limited time.
              By not selecting any permission from the list I will generate a token with all my permissions.

        Given I visit authorization page
        When I follow "Grant a new access"
        And I select "+2h" from "Select how long the token should be valid"
        And I fill in "description" with "Test token"
        And I follow "Create access token"
        And I copy the authorization token
        Then I close the popped modal

        Given I logout
        When I login using copied JWT
        And I visit users search page
        Then I should not see message "No permission to search for users"
