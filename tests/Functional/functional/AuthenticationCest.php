<?php declare(strict_types=1);

namespace Tests\Functional;

require_once __DIR__ . '/../Urls.php';

use FunctionalTester;
use http\Url;
use Tests\Urls;

class AuthenticationCest
{
    public function generateBasicToken(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createToken([
            'roles' => [
                'upload.images', 'upload.enforce_no_password', 'upload.enforce_tags_selected_in_token'
            ],
            'data' => [
                'tags' => ['gallery']
            ]
        ]);
        $I->canSeeResponseCodeIs(202);
        $I->storeIdAs('.tokenId', 'BASIC_TOKEN');
    }

    public function verifyTokenWasGeneratedByDoingLookup(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->lookupToken($I->getPreviouslyStoredIdOf('BASIC_TOKEN'));
        $I->canSeeResponseContainsJson([
            'tokenId' => $I->getPreviouslyStoredIdOf('BASIC_TOKEN')
        ]);
    }

    public function generateTokenWithLimitToSelectedTagsAndMimes(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createToken([
            'roles' => [
                'upload.images'
            ],
            'data' => [
                'tags' => ['user_uploads.u123', 'user_uploads'],
                'allowedMimeTypes'   => ['image/jpeg', 'image/png', 'image/gif'],
                'maxAllowedFileSize' => 14579
            ]
        ]);
        $I->canSeeResponseCodeIs(202);
        $I->storeIdAs('.tokenId', 'LIMITED_TOKEN');
    }

    public function verifyTheLimitedToken(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->lookupToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->canSeeResponseContainsJson([
            'tokenId'       => $I->getPreviouslyStoredIdOf('LIMITED_TOKEN'),
            'roles'         => ['upload.images'],
            'tags'          => ['user_uploads.u123', 'user_uploads'],
            'mimes'         => ['image/jpeg', 'image/png', 'image/gif'],
            'max_file_size' => 14579
        ]);
    }

    public function testJobClearExpiredTokensRuns(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->sendGET(Urls::JOB_CLEAR_EXPIRED_TOKENS);
        $I->canSeeResponseCodeIs(200);
    }

    public function testValidationContainsInvalidRoleName(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createToken([
            'roles' => ['upload.images', 'is-this-working?'],
            'data' => [
                'tags' => ['user_uploads.u123', 'user_uploads'],
                'allowedMimeTypes'   => ['image/jpeg', 'image/png', 'image/gif'],
                'maxAllowedFileSize' => 100
            ]
        ]);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('Please select valid roles');
    }

    public function testCannotGenerateTokensWhenRoleDoesNotAllowGeneratingTokens(FunctionalTester $I): void
    {
        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->createToken([
            'roles' => ['upload.images'],
            'data' => [
                'tags' => ['user_uploads.u123', 'user_uploads'],
                'allowedMimeTypes'   => ['image/jpeg', 'image/png', 'image/gif'],
                'maxAllowedFileSize' => 100
            ]
        ]);
        $I->canSeeResponseCodeIs(403);
        $I->canSeeResponseContains('Current token does not allow to generate tokens');
    }

    public function testCannotLookupTokensWhenHaveNoRightsGrantedToLookupTokens(FunctionalTester $I): void
    {
        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->lookupToken($I->getPreviouslyStoredIdOf('BASIC_TOKEN'));
        $I->canSeeResponseCodeIs(403);
    }

    public function testCannotRunCleanUpJobWhenHasNoPermissions(FunctionalTester $I): void
    {
        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->sendGET(Urls::JOB_CLEAR_EXPIRED_TOKENS);
        $I->canSeeResponseCodeIs(403);
    }

    public function testCanListRolesDocumentationWhenAdmin(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->sendGET(Urls::ROLES_LISTING);
        $I->canSeeResponseCodeIs(200);
    }

    public function testCanListRolesDocumentationWhenNonAdmin(FunctionalTester $I): void
    {
        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->sendGET(Urls::ROLES_LISTING);
        $I->canSeeResponseCodeIs(403);
    }

    public function testTryToDeleteGeneratedTokensAsNotAuthorizedUserToDeleteTokens(FunctionalTester $I): void
    {
        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->deleteToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->canSeeResponseCodeIs(403);

        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->deleteToken($I->getPreviouslyStoredIdOf('BASIC_TOKEN'));
        $I->canSeeResponseCodeIs(403);
    }

    public function testDeletePreviouslyGeneratedTokens(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->deleteToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->canSeeResponseCodeIs(200);

        $I->deleteToken($I->getPreviouslyStoredIdOf('BASIC_TOKEN'));
        $I->canSeeResponseCodeIs(200);
    }
}
