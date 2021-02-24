@bahub @server @realUseCase @backup @security
Feature: Backup Repository due to its granular permissions can be used as a Backup Hosting, where system administrator creates an user account for an organization,
         so that organization could manage it's backup collections. RiotKit official backup scenario.


    #
    # Before each scenario in this feature our assumptions are always the same
    #
    Background: As a system administrator I created a user account + one collection for organization.
                The organization have rights to manage it's collection: Edit, manage permissions,
                list users, upload version, list versions, delete version, download version

        # 1. Create a user for ORGANIZATION
        Given I am authenticated as administrator
        And I start creating user account "food-not-bombs@example.org" identified by "against-the-gov-and-capital" for organization "Food Not Bombs"
        When I check "collections.upload_to_allowed_collections"
        And I check "collections.list_versions_for_allowed_collections"
        And I check "collections.fetch_single_version_file_in_allowed_collections"
        And I check "collections.can_use_listing_endpoint"
        And I check "security.list_permissions"
        And I check "security.can_see_own_access_tokens"
        And I check "upload.all"
        Then I submit creation of user account

        # 2. Create a one collection
        Given I start creating collection "website.tar.gz" described "Website with a map of all Food Not Bombs initiatives all around the world" and "FIFO - delete oldest on adding new" strategy
        And I fill in "Max backups count" with "2"
        And I fill in "Max one version size" with "50MB"
        And I fill in "Max overall collection size" with "110MB"
        Then I press "Create" button
        And I copy id of a just created backup collection

        # 3. Give ORGANIZATION user access to created collection
        Given I visit collections page
        And I follow "website.tar.gz"
        And I start adding new permissions to collection for user "food-not-bombs@example.org"
        And I check "collections.upload_to_allowed_collections"
        And I check "collections.list_versions_for_allowed_collections"
        And I check "collections.fetch_single_version_file_in_allowed_collections"
        And I check "collections.can_use_listing_endpoint"
        And I check "collections.can_list_users_in_allowed_collections"
        Then I finalize adding new permissions to collection
        And I logout


    Scenario: As a organization user I can login with my login and password and edit collection attributes
        Given I login as "food-not-bombs@example.org" with "against-the-gov-and-capital"
        When I visit collections page
        And I follow "website.tar.gz"
        And I fill in "Description" with "Edited from E2E test! :-)"
        And I press "Save changes"
        Then the "Description" field should contain "Edited from E2E test! :-)"


    Scenario: As a organization user I can generate a token that will give access to my account
              but only for uploading and downloading backup versions, so I can use that token
              to backup and restore my files using Bahub shell client

        Given I login as "food-not-bombs@example.org" with "against-the-gov-and-capital"
        When I visit authorization page
        And I follow "Grant a new access"
        And I select "+7 days" from "Select how long the token should be valid"
        And I fill in "description" with "Limited token for Bahub usage"
        And I check "upload.all"
        And I check "collections.upload_to_allowed_collections"
        And I check "collections.fetch_single_version_file_in_allowed_collections"
        And I follow "Create access token"
        Then I should see "Access token generated"
        And I copy the authorization token

        # we created such collection in "Background" (before scenario)
        When I generate keys for existing backup configuration entry "fs"
        And I submit a new backup as part of "fs" definition for collection I recently created
        Then I expect bahub command finished with success

        When I issue a backup restore of "latest" version using "fs" definition for a collection I recently created
        Then I expect bahub command finished with success


