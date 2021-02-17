@bahub @backup @server
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
    And I submit a new backup as part of "fs" definition for collection I recently created
    And I reload the page
    Then I expect that there are "v1" backups present

    # 2. Verify that the backup can be restored
    When I issue a backup restore of "latest" version using "fs" definition for a collection I recently created
    Then I expect bahub command finished with success


Scenario: To keep storage space maintainable, every backup needs to be rotated according to the selected strategy
          So, when I upload a backup with "FIFO - delete oldest on adding new" strategy, then I expect that the oldest backup will be deleted after new backup will be uploaded

    Given I am authenticated as administrator
    And I visit backups page
    And I create a backup with filename="international-workers-association.tar.gz" description="IWA-AIT.org backup" strategy="FIFO - delete oldest on adding new" maxBackupsCount=3 maxOneVersionSize=50MB maxOverallCollectionSize=210MB
    And I generate a new access key with all permissions
    And I generate keys for existing backup configuration entry "fs"

    When I visit recently created collection page
    And I submit a new backup as part of "fs" definition for collection I recently created
    And I again submit a new backup as part of "fs" definition for collection I recently created
    And I again submit a new backup as part of "fs" definition for collection I recently created
    And I again submit a new backup as part of "fs" definition for collection I recently created
    And I again submit a new backup as part of "fs" definition for collection I recently created
    And I reload the page
    Then I expect that there are "v5, v4, v3" backups present
    And I expect that "v1, v2, v6" backups are not present


Scenario: To accidentally not overwrite any backup there should be a possibility to define a backup in a strategy "alert_when_too_many_versions"
          that will not rotate and raise error on uploading too many versions

    Given I am authenticated as administrator
    And I visit backups page
    And I create a backup with filename="strategy-two.tar.gz" description="Strategy two" strategy="Block on too many versions submitted" maxBackupsCount=1 maxOneVersionSize=50MB maxOverallCollectionSize=51MB
    And I generate a new access key with all permissions
    And I generate keys for existing backup configuration entry "fs"

    When I visit recently created collection page
    And I submit a new backup as part of "fs" definition for collection I recently created
    And I again submit a new backup as part of "fs" definition for collection I recently created
    And I reload the page
    Then I expect that there are "v1" backups present
    And I expect that "v2" backups are not present
    And I expect last bahub command output contains "Maximum count of files reached in the collection. Any of previous files should be deleted before uploading new"


Scenario: As a backup user I would expect that backup version will be possible to restore multiple times

    Given I am authenticated as administrator
    And I visit backups page
    And I create a backup with filename="strategy-two.tar.gz" description="Strategy two" strategy="Block on too many versions submitted" maxBackupsCount=1 maxOneVersionSize=50MB maxOverallCollectionSize=51MB
    And I generate a new access key with all permissions
    And I generate keys for existing backup configuration entry "fs"

    When I visit recently created collection page
    And I submit a new backup as part of "fs" definition for collection I recently created
    And I issue a backup restore of "latest" version using "fs" definition for a collection I recently created
    And I issue a backup restore of "latest" version using "fs" definition for a collection I recently created
    Then I expect last bahub command output contains "Successfully"
