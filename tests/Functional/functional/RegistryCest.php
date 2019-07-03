<?php declare(strict_types=1);

namespace Tests\Functional;

require_once __DIR__ . '/../Urls.php';

use FunctionalTester;

class RegistryCest
{
    private const SAMPLE_FILE = 'https://sample-videos.com/img/Sample-jpg-image-50kb.jpg';

    public function testUploadingByProvidingAnUrl(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.images']);

        $I->stopFollowingRedirects();
        $I->uploadByUrl(self::SAMPLE_FILE);
        $I->storeIdAs('filename', 'FILE_NAME');
        $I->canSeeResponseCodeIsSuccessful();
    }

    public function cannotDeletePreviouslyUploadedFileWhenDoNotHaveDeletionRights(FunctionalTester $I): void
    {
        $I->haveRoles(['collections.create_new']);

        $I->deleteFile($I->getPreviouslyStoredIdOf('FILE_NAME'));
        $I->canSeeResponseCodeIs(403);
    }

    public function deletePreviouslyUploadedFile(FunctionalTester $I): void
    {
        $I->haveRoles(['deletion.all_files_including_protected_and_unprotected']);

        $I->deleteFile($I->getPreviouslyStoredIdOf('FILE_NAME'));
        $I->canSeeResponseCodeIsSuccessful();
    }

    public function testIsNotAllowedToUploadSoWillNotUploadByUrl(FunctionalTester $I): void
    {
        $I->haveRoles(['collections.create_new']);

        $I->stopFollowingRedirects();
        $I->uploadByUrl(self::SAMPLE_FILE);
        $I->canSeeResponseCodeIs(403);
    }

    public function testReturnsValidationErrorWhenFileDoesNotExist(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->stopFollowingRedirects();
        $I->uploadByUrl(self::SAMPLE_FILE . 'xxxxxxxxxnotfoundxxxx');
        $I->canSeeResponseCodeIs(400);
    }

    public function testUploadAndDeletionWithPassword(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.images']);

        $I->stopFollowingRedirects();

        // zero case: upload a file with password
        $I->uploadByUrl(self::SAMPLE_FILE, [
            'password' => 'InternationalWorkersAssociation'
        ]);
        $I->storeIdAs('filename', 'FILE_NAME_WITH_PASSWORD');
        $I->canSeeResponseCodeIsSuccessful();

        // first case: without a valid password
        $I->deleteFile($I->getPreviouslyStoredIdOf('FILE_NAME_WITH_PASSWORD'), ['password' => 'invalid']);
        $I->canSeeResponseCodeIs(403);

        // second case: with permission g   ranted to delete all files regardless of if the password is valid
        $I->haveRoles(['deletion.all_files_including_protected_and_unprotected']);
        $I->deleteFile($I->getPreviouslyStoredIdOf('FILE_NAME_WITH_PASSWORD'), ['password' => 'invalid']);
        $I->canSeeResponseCodeIsSuccessful();
    }

    public function testValidationTooBigFileSubmitted(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all'], ['data' => ['maxAllowedFileSize' => 100]]);

        $I->uploadByPayload(str_repeat('Too big. ', 1024), [
            'fileName' => 'too-big.txt'
        ]);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('File size is too big');
    }

    public function testValidationMimeNotAllowed(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.images']);

        $I->uploadByPayload('some text file content there', ['fileName' => 'hello.txt']);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('Mime type not allowed');
    }
}
