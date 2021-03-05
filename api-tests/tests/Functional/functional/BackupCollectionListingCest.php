<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

/**
 * @group Domain/Backup
 */
class BackupCollectionListingCest
{
    public function testSearch(FunctionalTester $I): void
    {
        $I->amAdmin();

        // step 1: Create test data
        $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'description'       => 'Confederação Operária Brasileira database',
            'filename'          => 'cob_database.tar.gz'
        ]);
        $I->canSeeResponseCodeIsSuccessful();

        $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'description'       => 'Confederação Operária Brasileira uploads directory',
            'filename'          => 'cob_upload_files.tar.gz'
        ]);
        $I->canSeeResponseCodeIsSuccessful();

        // step 2: Search and expect
        $I->searchCollectionsFor([
            'createdTo' => '2050-05-05',
            'createdFrom' => (new \DateTime())->modify('-5 days')->format('Y-m-d'),
            'searchQuery' => 'Confederação Operária Brasileira*'
        ]);
        $I->expectToSeeCollectionsAmountOf(2);

        $I->searchCollectionsFor([
            'createdTo' => '2050-05-05',
            'createdFrom' => (new \DateTime())->modify('-5 days')->format('Y-m-d'),
            'searchQuery' => '*uploads directory*'
        ]);
        $I->expectToSeeCollectionsAmountOf(1);

        // step 3: Check next page, even without filters, should be empty if only 2-3 files are stored
        $I->searchCollectionsFor([
            'page'  => 2,
            'limit' => 50
        ]);
        $I->expectToSeeCollectionsAmountOf(0);
    }
}
