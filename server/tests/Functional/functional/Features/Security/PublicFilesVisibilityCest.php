<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

use FunctionalTester;

/**
 * FEATURE: Public files should be visible for guests without any token entered
 */
class PublicFilesVisibilityCest
{
    private const TEXT = 'Each individual is a cosmos of organs, each organ is a cosmos of cells, 
        each cell is a cosmos of infinitely small ones; and in this complex world, 
        the well-being of the whole depends entirely on the sum of well-being enjoyed
        by each of the least microscopic particles of organized matter. A whole 
        revolution is thus produced in the philosophy of life.';

    /**
     * Case: Files uploaded with "public: true" should be accessible to anyone
     *
     * @param FunctionalTester $I
     */
    public function asAGuestIShouldBeAbleToAccessAPublicFile(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->uploadByPayload(self::TEXT, ['fileName' => 'kropotkin-1.txt', 'public' => true]);
        $I->storeIdAs('.filename', 'FILENAME');

        // test as guest
        $I->amGuest();
        $I->fetchFile($I->getPreviouslyStoredIdOf('FILENAME'));
        $I->seeResponseContains('the well-being of the whole depends entirely on the sum of well-being enjoyed');
        $I->canSeeResponseCodeIs(200);
    }

    /**
     * Case: The "public: false" files should not be accessed by people without any access token
     *
     * @param FunctionalTester $I
     */
    public function asGuestIShouldNotSeeNonPublicFile(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->uploadByPayload(self::TEXT, ['fileName' => 'non-public-file.txt', 'public' => false]);
        $I->storeIdAs('.filename', 'NON_PUBLIC_FILENAME');

        // as guest
        $I->amGuest();
        $I->fetchFile($I->getPreviouslyStoredIdOf('NON_PUBLIC_FILENAME'));
        $I->canSeeResponseCodeIs(403);
    }

    /**
     * Case: As guest and non-guest I should be able to access password protected files, only if I enter correct password
     *
     * @param FunctionalTester $I
     */
    public function asGuestIShouldNotBeAbleToViewPasswordProtectedFileUntilIEnterThePassword(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->uploadByPayload(self::TEXT, ['fileName' => 'non-public-file.txt', 'public' => true, 'password' => 'kropotkin']);
        $I->storeIdAs('.filename', 'PASSWORD_1');

        $I->amGuest();

        // 1) Invalid password
        $I->fetchFile($I->getPreviouslyStoredIdOf('PASSWORD_1'), ['password' => 'invalid']);
        $I->canSeeResponseCodeIs(403);

        // 2) Correct password
        $I->fetchFile($I->getPreviouslyStoredIdOf('PASSWORD_1'), ['password' => 'kropotkin']);
        $I->canSeeResponseCodeIs(200);
    }

    public function asAUploaderIShouldHaveReadPermissionsToTheUploadedFile(FunctionalTester $I): void
    {
        // create two tokens
        $I->amGoingTo('create a non-privileged access token');
        $I->amAdmin();
        $I->createToken(['roles' => ['upload.images']], false);
        $I->storeIdAs('.token.id', 'NON_PRIVILEGED_TOKEN');

        $I->amGoingTo('create a second non-privileged token');
        $I->createToken(['roles' => ['upload.images']], false);
        $I->storeIdAs('.token.id', 'NON_PRIVILEGED_TOKEN_2');

        // become a non-privileged user
        $I->amGoingTo('become a non-privileged user');
        $I->amToken($I->getPreviouslyStoredIdOf('NON_PRIVILEGED_TOKEN'));

        // upload a file
        $I->uploadByPayload(self::TEXT, ['fileName' => 'kropotkin.txt', 'public' => false]); // important: file cannot be public
        $I->storeIdAs('.filename', 'NON_PRIVILEGED_TOKEN_TEST_FILE');

        // verify as uploader
        $I->amToken($I->getPreviouslyStoredIdOf('NON_PRIVILEGED_TOKEN'));
        $I->fetchFile($I->getPreviouslyStoredIdOf('NON_PRIVILEGED_TOKEN_TEST_FILE'));
        $I->canSeeResponseCodeIs(200);

        // verify as non-uploader: Should have no access (not granted in any way, the token has no roles assigned, file is not public)
        $I->amToken($I->getPreviouslyStoredIdOf('NON_PRIVILEGED_TOKEN_2'));
        $I->fetchFile($I->getPreviouslyStoredIdOf('NON_PRIVILEGED_TOKEN_TEST_FILE'));
        $I->canSeeResponseCodeIs(403);
    }
}
