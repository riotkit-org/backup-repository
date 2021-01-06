Feature: I can manage my own access list

    Background:
        Given I am authenticated as administrator

    Scenario: I can revoke current session, so then I expect that I will be logged out
        Given I visit authorization page
        And I follow "Revoke"
        Then I should see message "No active token found"


    Scenario: As a user with all permissions I can generate a new API token for longer period than 1 hour,
              then I can revoke that token

        Given I visit authorization page
        And I follow "Grant a new access"
        And I select "+7 days" from "ttl"
        And I fill in "description" with "Test token"
        And I follow "Create access token"
        Then I should see "Access token generated"

        When I close the popped modal
        And I revoke the token "Test token"
        Then I should see message "Access revoked"
        And I expect the Revoke button will be disabled for "Test token"
