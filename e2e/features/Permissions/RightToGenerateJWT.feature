@server @security
Feature: In order to limit already generated JWT's lifetime there must be no possibility to generate a new JWT using current JWT.
         Unless the user grants that action on that first JWT.

    Background:
        Given I am authenticated as administrator

    Scenario: As a user I generate a JWT that does not have access rights to generate next JWT (JWT->JWT)
        Given I visit authorization page
        When I follow "Grant a new access"
        And I select "+7 days" from "Select how long the token should be valid"
        And I follow "collections.can_use_listing_endpoint"
        And I follow "collections.view_all_collections"
        And I check "security.list_permissions"
        And I check "security.can_see_own_access_tokens"
        And I fill in "description" with "Test token"
        And I follow "Create access token"
        And I copy the authorization token
        Then I close the popped modal

        When I logout
        And I login using copied JWT
        And I visit authorization page
        And I follow "Grant a new access"
        And I select "+7 days" from "Select how long the token should be valid"
        And I follow "collections.can_use_listing_endpoint"
        And I follow "Create access token"
        Then I should see message "Cannot create an API token. Possible reasons: 1) selected permissions that user actually does not have. You can only limit your permissions 2) Your current JWT does not allow generating new JWTs"


    Scenario: As a user I generate a JWT that has full access to my account, so it can also generate next JWTs
        Given I visit authorization page
        When I follow "Grant a new access"
        And I select "+7 days" from "Select how long the token should be valid"
        # do not select any role means selecting all
        And I fill in "description" with "Test token"
        And I follow "Create access token"
        And I copy the authorization token
        Then I close the popped modal

        When I logout
        And I login using copied JWT
        And I visit authorization page
        And I follow "Grant a new access"
        And I select "+7 days" from "Select how long the token should be valid"
        And I follow "collections.can_use_listing_endpoint"
        And I follow "Create access token"
        Then I should not see message "Cannot create an API token. Possible reasons: 1) selected permissions that user actually does not have. You can only limit your permissions 2) Your current JWT does not allow generating new JWTs"

