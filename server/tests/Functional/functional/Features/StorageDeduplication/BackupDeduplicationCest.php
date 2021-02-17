<?php declare(strict_types=1);

namespace Tests\Functional\Features\StorageDeduplication;

use FunctionalTester;

/**
 * FEATURE: Deduplication feature in backups domain
 *
 * @group Domain/Backup
 */
class BackupDeduplicationCest
{
    private string $firstCollection;
    private string $secondCollection;

    public function seedData(FunctionalTester $I): void
    {
        $I->amAdmin();
        $this->firstCollection = $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'description'       => 'Confederação Operária Brasileira database',
            'filename'          => 'cob_database.tar.gz'
        ]);

        $this->secondCollection = $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'description'       => 'Confederação Operária Brasileira database #2',
            'filename'          => 'cob_database-2.tar.gz'
        ]);

        $I->uploadToCollection(
            $this->firstCollection,
            "ZSP-IWA calls for a week of protest action against the repression of workers from the Post Office in Poland"
        );
        $I->storeIdAs('.version.id', 'FIRST_COLLECTION_VERSION_ID');

        $I->uploadToCollection(
            $this->secondCollection,
            "ZSP-IWA calls for a week of protest action against the repression of workers from the Post Office in Poland"
        );
        $I->storeIdAs('.version.id', 'SECOND_COLLECTION_VERSION_ID');
    }

    public function deletingOneVersionWillKeepOthers(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->deleteVersionFromCollection($this->firstCollection, $I->getPreviouslyStoredIdOf('FIRST_COLLECTION_VERSION_ID'));

        // the file in second collection should not be affected
        $I->downloadCollectionVersion($this->secondCollection,$I->getPreviouslyStoredIdOf('SECOND_COLLECTION_VERSION_ID'));
        $I->canSeeResponseContains('ZSP-IWA');
    }
}
