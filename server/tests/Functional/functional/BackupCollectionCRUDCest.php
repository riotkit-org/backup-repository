<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

/**
 * @group Domain/Backup
 */
class BackupCollectionCRUDCest
{
    public function testCreateEditDelete(FunctionalTester $I): void
    {
        $I->haveRoles([
            'collections.create_new',
            'collections.manage_tokens_in_allowed_collections',
            'collections.delete_allowed_collections',
            'collections.modify_details_of_allowed_collections'
        ], [
            'data' => [
                'tags'               => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 145790
            ]
        ]);

        // step 1: Create a collection
        $id = $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'solfed.org.uk_database.tar.gz'
        ]);
        $I->canSeeResponseCodeIsSuccessful();

        // step 2: Update the collection
        $I->updateCollection($id, [
            'maxBackupsCount'   => 5,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'solfed.org.uk_database.tar.gz'
        ]);
        $I->canSeeResponseCodeIsSuccessful();

        // step 3: Check validation, if it works for editing, not only for creating
        $I->updateCollection($id, [
            'maxBackupsCount'   => 99999,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'solfed.org.uk_database.tar.gz'
        ]);
        $I->canSeeResponseCodeIs(400);

        // step 4: Delete the collection
        $I->deleteCollection($id);
        $I->canSeeResponseCodeIsSuccessful();

        // step 5: Try to edit deleted collection without success
        $I->updateCollection($id, [
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'solfed.org.uk_database.tar.gz'
        ]);
        $I->canSeeResponseCodeIs(404);
        $I->canSeeResponseContains('Object not found');
    }

    public function testFetchCollectionMetaData(FunctionalTester $I): void
    {
        $I->haveRoles(['collections.create_new'],
            [
                'data' => [
                    'tags'               => ['user_uploads.u123', 'user_uploads'],
                    'maxAllowedFileSize' => 145790
            ]
        ]);

        $id = $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'solfed.org.uk_database.tar.gz'
        ]);

        $I->fetchCollection($id);
        $I->canSeeResponseContains('"filename": "solfed.org.uk_database.tar.gz"');
    }

    public function testFetchNonExistingCollectionEndsWithNotFound(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->fetchCollection('some-non-existing');
        $I->canSeeResponseCodeIs(404);
        $I->canSeeResponseContains('Object not found');
    }
}
