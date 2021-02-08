@bahub @backup
Feature: From a perspective of a end-user I would like to be able to send any data in encrypted manner
         When sending the data multiple times it should rotate and be versioned
         I need to be able to receive my last 3 sent copies back anytime I request by specifying a version number
         or a tag "latest"

Scenario: As an system administrator I will upload a backup, and then immediately restore
          it back to check how "latest" backup can be restored

    Given I am authenticated as administrator
    And I visit backups page
    And I create a backup with filename="international-workers-association.tar.gz" description="IWA-AIT.org backup" strategy="FIFO - delete oldest on adding new" maxBackupsCount=2 maxOneVersionSize=50MB maxOverallCollectionSize=110MB
    And I generate a new access key with all permissions
    And I generate keys for existing backup configuration entry "fs"

    # 1. Verify that the backup was sent
    When I visit recently created collection page
    And I submit a new backup as part of "fs" definition for collection I just created
    And I reload the page
    Then I expect that there is "v1" backup present

    # 2. Verify that the backup can be restored
    When I issue a backup restore of "latest" version using "fs" definition for a collection I recently created
    Then I expect bahub command finished with success
