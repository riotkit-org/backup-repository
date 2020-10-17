<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

/**
 * @group Domain/Backup
 */
class BackupCollectionUploadAlertOnLimitStrategyCest
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \User
     */
    private $user;

    public function prepareDataForTest(FunctionalTester $I): void
    {
        $I->amAdmin();
        $this->user = $I->createStandardUser([
            'roles' => [
                "collections.create_new",
                "collections.manage_tokens_in_allowed_collections",
                "collections.upload_to_allowed_collections",
                "collections.list_versions_for_allowed_collections",
                "upload.all"
            ]
        ]);

        $I->amUser($this->user->email, $this->user->password);

        $this->id = $I->createCollection([
            "maxBackupsCount"   => 2,
            "maxOneVersionSize" => "1MB",
            "maxCollectionSize" => "5MB",
            "strategy"    => "alert_when_backup_limit_reached",
            "description" => "Title: Brighton Solidarity Federation: the first five months of 2018",
            "password"    => "solfed",
            "filename"    => "solfed-state"
        ]);
    }

    public function testAlertsWhenCollectionIsFull(FunctionalTester $I): void
    {
        $I->amUser($this->user->email, $this->user->password);
        $I->uploadToCollection($this->id, "............... Good, v1");
        $I->seeResponseCodeIsSuccessful();

        $I->uploadToCollection($this->id, "............... Very good, v2");
        $I->seeResponseCodeIsSuccessful();

        $I->uploadToCollection($this->id, "............... Cannot upload v3, only two versions allowed");
        $I->canSeeResponseCodeIsClientError();
        $I->canSeeResponseContainsJson([
            "error" => "Maximum count of files reached in the collection. Any of previous files should be deleted before uploading new",
            "code" => 41012,
            "type" => "validation.error"
        ]);
    }
}
