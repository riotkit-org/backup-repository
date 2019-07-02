<?php declare(strict_types=1);

namespace Tests\Functional;

require_once __DIR__ . '/../Urls.php';

use FunctionalTester;

class RegistryCest
{
    public function testUploadingByProvidingAnUrl(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->amToken(
            $I->createToken([
                'roles' => ['upload.images']
            ])
        );

        $I->stopFollowingRedirects();
        $I->uploadByUrl('http://zsp.net.pl/files/barroness_logo.png');
        $I->canSeeResponseCodeIsSuccessful();
    }

    public function testIsNotAllowedToUploadSoWillNotUploadByUrl(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->amToken(
            $I->createToken([
                'roles' => ['collections.create_new']
            ])
        );

        $I->stopFollowingRedirects();
        $I->uploadByUrl('http://zsp.net.pl/files/barroness_logo.png');
        $I->canSeeResponseCodeIs(403);
    }

    public function testReturnsValidationErrorWhenFileDoesNotExist(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->stopFollowingRedirects();
        $I->uploadByUrl('http://zsp.net.pl/files/barroness_logo.pngxxxxxxxxxnotfoundxxxx');
        $I->canSeeResponseCodeIs(400);
    }
}
