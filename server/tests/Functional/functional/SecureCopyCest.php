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
                'secureCopyEncryptionMethod' => 'aes-256-cbc',
                'secureCopyEncryptionKey'    => 'Worlds-26-richest-people-control-as-much-as-3.8-billion-people',
                'secureCopyDigestMethod'     => 'sha512',
                'secureCopyDigestRounds'     => 6000,
                'secureCopyDigestSalt'       => 'test'
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
                'secureCopyEncryptionKey'    => 'class-struggle-36',
                'secureCopyDigestMethod'     => 'sha512',
                'secureCopyDigestRounds'     => 6000,
                'secureCopyDigestSalt'       => 'test'
            ]
        ], false);
        $I->canSeeResponseContains('form.data.secureCopyEncryptionMethod');
        $I->canSeeResponseCodeIs(400);
    }

    public function checkFormForEachFileIsEncryptedWhenTokenHasEncryptionDetails(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyEncryptionMethod' => 'aes-256-cbc',
                'secureCopyEncryptionKey'    =>
                    'Gentrification-is-a-process-of-pushing-less-affluent-people-' .
                    'out-of-the-city-by-rising-up-costs-of-living-but-not-their-wages',
                'secureCopyDigestMethod'     => 'sha512',
                'secureCopyDigestRounds'     => 6000,
                'secureCopyDigestSalt'       => 'test'
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
                'secureCopyEncryptionMethod' => 'aes-256-cbc',
                'secureCopyEncryptionKey'    =>
                    'Gentrification-is-a-process-of-pushing-less-affluent-people-' .
                    'out-of-the-city-by-rising-up-costs-of-living-but-not-their-wages',
                'secureCopyDigestMethod'     => 'sha512',
                'secureCopyDigestRounds'     => 6000,
                'secureCopyDigestSalt'       => 'test'
            ]
        ]);

        $I->retrieveFileMetadataFromSecureCopy($realId);
        $I->canSeeResponseCodeIs(404);
    }

    /**
     * Case: GIVEN I see a list of files
     *       AND the list has encrypted form field
     *       WHEN I try to decrypt the form field using OpenSSL shell command
     *       THEN I should be able to see the decrypted json contents
     *
     * @param FunctionalTester $I
     */
    public function testCanDecryptFormUsingOpenSSLOutsideOfPHP(FunctionalTester $I): void
    {
        $key = 'Gentrification-is-a-process-of-pushing-less-affluent-people-' .
            'out-of-the-city-by-rising-up-costs-of-living-but-not-their-wages';

        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyDigestMethod'     => 'sha512',
                'secureCopyDigestRounds'     => 6000,
                'secureCopyDigestSalt'       => 'test',
                'secureCopyEncryptionMethod' => 'aes-256-cbc',
                'secureCopyEncryptionKey'    => $key
            ]
        ]);

        $I->receiveListOfElementsFromSecureCopy('file');

        // parse response, extract base64 encoded IV + CipherText
        $entries = explode("\n", $I->grabResponse());
        $last    = json_decode($entries[count($entries) - 2], true);
        $form    = base64_decode($last['form']['encrypted']);
        $pos     = mb_strpos($form, '@RiotKit_FR@');
        $hexIV   = mb_substr($form, 0, $pos);
        $cipherText = mb_substr($form, $pos + strlen('@RiotKit_FR@'));

        $decrypted = $this->decrypt($cipherText, 'sha512', 6000, 'aes-256-cbc', $key, $hexIV);
        $json      = @json_decode($decrypted, true);

        $I->assertTrue(
            is_array($json),
            'Expected, that decoded text will be a valid JSON array'
        );
    }

    /**
     * Case: GIVEN we have a token for downloading files in secure copy
     *       AND token has encryption
     *       WHEN I download a file
     *       AND I try to decrypt it using a same key used to encrypt it, but using OpenSSL shell tool
     *       THEN I should be able to decrypt the file and see its contents
     *
     * @param FunctionalTester $I
     */
    public function testCanDecryptFileContentUsingOpenSSLOutsideOfPHP(FunctionalTester $I): void
    {
        $key = 'An independent contractor is not entitled to minimum wage, overtime, insurance, protection, ' .
               'or other employee rights. Attempts are sometimes made to define ' .
               'ordinary employees as independent contractors.';

        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyDigestMethod'     => 'sha512',
                'secureCopyDigestRounds'     => 6000,
                'secureCopyDigestSalt'       => 'test',
                'secureCopyEncryptionMethod' => 'aes-256-cbc',
                'secureCopyEncryptionKey'    => $key
            ]
        ]);

        $I->receiveListOfElementsFromSecureCopy('file');

        // parse response, extract base64 encoded IV + CipherText
        $entries = explode("\n", $I->grabResponse());
        $last    = json_decode($entries[count($entries) - 2], true);
        $lastFileId = $last['id'];

        // CipherText and IV
        $cipherText = $I->downloadFileFromSecureCopy($lastFileId);
        $iv = $I->grabHttpHeader('Encryption-Initialization-Vector');

        $decrypted = $this->decrypt($cipherText, 'sha512', 6000, 'aes-256-cbc', $key, $iv);
        $I->assertSame(
            'and enable themselves to administer them jointly, in such a way that they will be able to continue production and social life.',
            trim($decrypted),
            'Expect that part of IWA statute will be returned as part of decoded data'
        );
    }

    /**
     * Helper method that decrypts cipher using OpenSSL shell tool
     * (Different implementation than PHP. That's important, that the cipher can be decoded outside of File Repository!)
     *
     * @param string $cipherText
     * @param string $digestAlgorithm
     * @param int $digestRounds
     * @param string $algorithm
     * @param string $key
     * @param string $iv
     *
     * @return string
     */
    private function decrypt(string $cipherText, string $digestAlgorithm, int $digestRounds, string $algorithm, string $key, string $iv): string
    {
        $hashedKey = openssl_pbkdf2($key, $iv, 16, $digestRounds, $digestAlgorithm);

        $tmpFileDir = tempnam(sys_get_temp_dir(), 'file-repository');
        file_put_contents($tmpFileDir, $cipherText);

        // decode using OpenSSL shell tool to prove that application encoded it correctly
        return shell_exec(
            'cat ' . $tmpFileDir . ' | openssl enc -d -' . $algorithm . ' -pbkdf2 -iter ' .
            ' ' . $digestRounds . ' -salt -md ' . $digestAlgorithm . ' -K ' . bin2hex($hashedKey) . ' -iv ' . $iv . ' ' .
            ' 2>/dev/null'
        );
    }
}
