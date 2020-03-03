<?php declare(strict_types=1);

namespace Tests\Functional\Features\StorageDeduplication;

use FunctionalTester;

/**
 * FEATURE: Deduplication feature in plain storage
 */
class StorageDeduplicationCest
{
    private function seedStorageWithData(FunctionalTester $I): void
    {
        $text = '
            Revolutionary Unionism rejects all political and national frontiers, which are arbitrarily created, 
            and declares that so-called nationalism is just the religion of the modern state, behind which is
            concealed the material interests of the propertied classes. Revolutionary unionism recognizes only 
            economic differences, whether regional or national, that produce hierarchies, privileges and every 
            kind of oppressions (because of race, sex and any false or real difference), and in the spirit of 
            solidarity claims the right to self-determination for all economic groups.
        ';

        $I->amAdmin();
        $I->uploadByPayload($text, [
            'fileName' => 'iwa-statute-part.txt',
            'public'   => true
        ]);
        $I->storeIdAs('.filename', 'FIRST_FILENAME');

        $I->uploadByPayload($text, [
            'fileName' => 'iwa-other-file',
            'public'   => true
        ]);
        $I->storeIdAs('.filename', 'SECOND_FILENAME');

        $I->uploadByPayload($text, [
            'fileName' => 'iwa-third-duplicated-file.txt',
            'public'   => true
        ]);
        $I->storeIdAs('.filename', 'THIRD_FILENAME');
    }

    public function checkIfSameFileSubmittedTwoTimesIsMarkedAsUnique(FunctionalTester $I): void
    {
        $this->seedStorageWithData($I);

        $I->amAdmin();
        $I->listFiles(['page' => 1, 'limit' => 50]);

        $first = $I->grabDataFromResponseByJsonPath('.results[0].attributes.path');
        $second = $I->grabDataFromResponseByJsonPath('.results[1].attributes.path');

        $I->assertSame($first, $second, 'Expected that the storage path will be the same for two submitted files of same content, but under different name.');
    }

    public function testThatDeletingOneFileDoesNotDeleteAllFiles(FunctionalTester $I): void
    {
        $id = $I->getPreviouslyStoredIdOf('FIRST_FILENAME');
        $secondId = $I->getPreviouslyStoredIdOf('SECOND_FILENAME');

        $I->amAdmin();
        $I->deleteFile($id);

        $I->fetchFile($secondId);
        $I->canSeeResponseContains('Revolutionary Unionism');
    }

    public function checkThatDeletingSecondCopyWillNotBreakThirdCopy(FunctionalTester $I): void
    {
        $secondId = $I->getPreviouslyStoredIdOf('SECOND_FILENAME');
        $thirdId = $I->getPreviouslyStoredIdOf('THIRD_FILENAME');

        $I->amAdmin();
        $I->deleteFile($secondId);
        $I->canSeeResponseCodeIs(200);

        $I->fetchFile($thirdId);
        $I->canSeeResponseContains('Revolutionary Unionism');

        $I->fetchFile($secondId);
        $I->canSeeResponseCodeIs(404);
    }

    public function checkThatWhenWeDeleteAllThreeCopiesThenNoFileCouldBeFound(FunctionalTester $I): void
    {
        $firstId = $I->getPreviouslyStoredIdOf('FIRST_FILENAME');
        $secondId = $I->getPreviouslyStoredIdOf('SECOND_FILENAME');
        $thirdId = $I->getPreviouslyStoredIdOf('THIRD_FILENAME');

        $I->amAdmin();
        $I->deleteFile($thirdId);

        foreach ([$firstId, $secondId, $thirdId] as $id) {
            $I->fetchFile($id);
            $I->canSeeResponseCodeIs(404);
        }
    }
}
