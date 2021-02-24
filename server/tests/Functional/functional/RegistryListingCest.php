<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

/**
 * @group Domain/Storage
 * @group Technical
 */
class RegistryListingCest
{
    private function populateWithSomeData(FunctionalTester $I): void
    {
        $I->amAdmin();

        $collectionId = $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'zsp.net.pl_database.tar.gz'
        ]);

        $I->uploadToCollection($collectionId,
            "12th - 18th October. International Week against Unpaid Wages"
        );
    }

    public function testListingAllFiles(FunctionalTester $I): void
    {
        $this->populateWithSomeData($I);
        $I->havePermissions(['admin.view.files_from_all_tags', 'admin.view.can_use_listing_endpoint_at_all']);

        $I->listFiles(['page' => 1, 'limit' => 20]);
        $I->canSeeResponseContains('zsp.net.pl_database.tar-v1.gz');
    }

    public function testFindByName(FunctionalTester $I): void
    {
        $this->populateWithSomeData($I);
        $I->havePermissions(['admin.view.files_from_all_tags', 'admin.view.can_use_listing_endpoint_at_all']);

        // case 1: will find, because "hello.txt" was uploaded
        $I->listFiles(['page' => 1, 'limit' => 20, 'searchQuery' => 'zsp.net.pl']);
        $I->canSeeResponseContains('zsp.net.pl');

        // case 2: will not find, as the file with similar name was not uploaded yet
        $I->listFiles(['page' => 1, 'limit' => 20, 'searchQuery' => 'non-existing.txt']);
        $I->cantSeeResponseContains('non-existing.txt');
    }
}
