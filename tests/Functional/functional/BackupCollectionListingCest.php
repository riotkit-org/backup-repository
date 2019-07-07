<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

class BackupCollectionListingCest
{
    public function testFindsByWildcard(FunctionalTester $I): void
    {
        $I->amAdmin();

        // step 1: Create test data
        $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'description'       => 'Solidarity Federation database',
            'filename'          => 'solfed.org.uk_database.tar.gz'
        ]);
        $I->canSeeResponseCodeIsSuccessful();

        $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'description'       => 'Solidarity Federation uploads directory',
            'filename'          => 'solfed.org.uk_upload_files.tar.gz'
        ]);
        $I->canSeeResponseCodeIsSuccessful();

        // step 2: Search and expect
        $I->searchCollectionsFor([
            'createdTo' => '2050-05-05',
            'createdFrom' => (new \DateTime())->modify('-5 days')->format('Y-m-d'),
            'searchQuery' => 'Solidarity Federation*'
        ]);
        $I->expectToSeeCollectionsAmountOf(2);

        $I->searchCollectionsFor([
            'createdTo' => '2050-05-05',
            'createdFrom' => (new \DateTime())->modify('-5 days')->format('Y-m-d'),
            'searchQuery' => '*uploads directory*'
        ]);
        $I->expectToSeeCollectionsAmountOf(1);
    }
}
