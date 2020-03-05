<?php declare(strict_types=1);

namespace Tests\Functional;

require_once __DIR__ . '/../Urls.php';

use FunctionalTester;
use Tests\Urls;

class FilenameValidationCest
{
    public function verifyValidationErrorWillBeReturned(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->sendPOST(Urls::URL_REPOSITORY_FILE_UPLOAD . '?fileName=;\\\\@invalidfilename@@@&stripInvalidCharacters=false', str_repeat('IWA-AIT ', 8096));
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('Filename is not valid');
    }

    public function verifyValidFileNamePassedValidation(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->sendPOST(Urls::URL_REPOSITORY_FILE_UPLOAD . '?fileName=1235Cdwqe3_anarchista_z_przypadku.mp4&stripInvalidCharacters=false', null, [
            'test' => __FILE__
        ]);
        $I->canSeeResponseContains('"status": "OK"');
    }
}
