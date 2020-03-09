<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;
use Tests\Urls;

class SecureCopyCest
{
    public function seedData(FunctionalTester $I): void
    {
        $exampleTextData = "Against the offensive of Capital and politicians of all hues, 
            all the revolutionary workers of the world must build a real International Workers’ Association, in which, 
            each member will know that the emancipation of the working class will only be possible when the workers themselves, 
            in their capacities as producers, manage to prepare themselves in their political-economic organizations to take possession of the land and the factories 
            and enable themselves to administer them jointly, in such a way that they will be able to continue production and social life.";

        $dataSplitByLines = explode("\n", $exampleTextData);

        $I->haveRoles(['upload.all']);

        foreach ($dataSplitByLines as $lineNum => $line) {
            $I->uploadByPayload($line, ['fileName' => '+line_' . $lineNum . '-iwa-statute.txt']);
            $I->canSeeResponseCodeIs(200);
        }
    }

    /**
     * Case: Order is very important as the entries needs to be downloaded chronologically
     *
     * @param FunctionalTester $I
     */
    public function testOrderOnTheListMatchesChronology(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->receiveListOfElementsFromSecureCopy('file');

        $list = explode("\n", $I->grabResponse());
        $elementsOnly = array_filter($list, function (string $line) {
            return substr(trim($line), 0, 1) === '{' && isset(json_decode($line, true)['id']);
        });
        $listOfNames = array_map(
            function (string $line) {
                $asArray = json_decode($line, true);
                return explode('+', $asArray['id'])[1];
            },
            $elementsOnly
        );

        $I->assertSame(
            [
                'line_0-iwa-statute.txt',
                'line_1-iwa-statute.txt',
                'line_2-iwa-statute.txt',
                'line_3-iwa-statute.txt',
                'line_4-iwa-statute.txt'
            ],
            array_values($listOfNames)
        );
    }

    public function testAdminCanAccessSecureCopy(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(200);
    }

    public function testUserWithoutSecureCopyRoleCannotSeeSecureCopyList(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all']);
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(403);
    }

    public function testUserWithSecureCopyRoleCanSeeSecureCopyList(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream']);
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(200);
    }

    public function testCreateTokenWithEncryptionPassword(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyEncryptionMethod' => 'aes-128-cbc',
                'secureCopyEncryptionKey'    => 'Worlds-26-richest-people-control-as-much-as-3.8-billion-people'
            ]
        ]);
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(200);
    }

    public function testEncryptionMethodValueValidation(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyEncryptionMethod' => 'SOME',
                'secureCopyEncryptionKey'    => 'class-struggle-36'
            ]
        ], false);
        $I->canSeeResponseContains('form.data.secureCopyEncryptionMethod');
        $I->canSeeResponseCodeIs(400);
    }

    public function checkFormForEachFileIsEncryptedWhenTokenHasEncryptionDetails(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyEncryptionMethod' => 'aes-128-cbc',
                'secureCopyEncryptionKey'    =>
                    'Gentrification-is-a-process-of-pushing-less-affluent-people-' .
                    'out-of-the-city-by-rising-up-costs-of-living-but-not-their-wages'
            ]
        ]);
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(200);
        $I->canSeeResponseContains('"form":{"encrypted":');  // expect that the form will be encrypted
    }

    public function checkFormIsNotEncryptedIfTokenHasNoEncryptionFilledUp(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => []
        ]);
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(200);
        $I->cantSeeResponseContains('"form":{"encrypted":');
    }

    public function testThatTokenWithCryptoCannotAccessFilesByTheirOriginalPlainNames(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->receiveListOfElementsFromSecureCopy('file');

        $entries = explode("\n", $I->grabResponse());
        $last = json_decode($entries[count($entries) - 2], true);

        // this is a real id, not encrypted
        $realId = $last['id'];


        // as admin, without encryption turned on I should be able to see the file under original name
        $I->retrieveFileMetadataFromSecureCopy($realId);
        $I->canSeeResponseCodeIs(200);

        // re-login into non-privileged token
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyEncryptionMethod' => 'aes-128-cbc',
                'secureCopyEncryptionKey'    =>
                    'Gentrification-is-a-process-of-pushing-less-affluent-people-' .
                    'out-of-the-city-by-rising-up-costs-of-living-but-not-their-wages'
            ]
        ]);

        $I->retrieveFileMetadataFromSecureCopy($realId);
        $I->canSeeResponseCodeIs(404);
    }
}
