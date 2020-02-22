<?php declare(strict_types=1);

namespace Tests\Functional;

require_once __DIR__ . '/../Urls.php';

use FunctionalTester;
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
        ], false);
        $I->canSeeResponseCodeIs(201);
        $I->storeIdAs('.token.id', 'BASIC_TOKEN');
    }

    public function verifyTokenWasGeneratedTodoSoUseLookupEndpoint(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->lookupToken($I->getPreviouslyStoredIdOf('BASIC_TOKEN'));
        $I->canSeeResponseContainsJson([
            'token' => [
                'id' => $I->getPreviouslyStoredIdOf('BASIC_TOKEN')
            ]
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
        ], false);
        $I->canSeeResponseCodeIs(201);
        $I->storeIdAs('.token.id', 'LIMITED_TOKEN');
    }

    public function verifyTheLimitedToken(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->lookupToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->canSeeResponseContainsJson([
            'token' => [
                'id' => $I->getPreviouslyStoredIdOf('LIMITED_TOKEN'),
                'active' => true,
                'roles'  => ['upload.images'],
                'data'   => [
                    'tags'               => ['user_uploads.u123', 'user_uploads'],
                    'allowedMimeTypes'   => ['image/jpeg', 'image/png', 'image/gif'],
                    'maxAllowedFileSize' => 14579
                ]
            ]
        ]);
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
        ], false);
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
        ], false);
        $I->canSeeResponseCodeIs(403);
        $I->canSeeResponseContains('Current token does not allow to generate tokens');
    }

    public function testCannotLookupTokensWhenHaveNoRightsGrantedToLookupTokens(FunctionalTester $I): void
    {
        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN'));
        $I->lookupToken($I->getPreviouslyStoredIdOf('BASIC_TOKEN'));
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

    /**
     * Feature: Possibility to set "id" of a token manually
     * Case: Successful case
     *
     * @param FunctionalTester $I
     */
    public function testGenerateTokenWithCustomIdSpecifiedInRequest(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createToken([
            'roles' => [
                'upload.images'
            ],
            'data' => [],
            'id'   => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
        ], false);
        $I->canSeeResponseCodeIs(201);
    }

    /**
     * Feature: Possibility to set "id" of a token manually
     * Case: Trying to create same token at least twice
     *
     * @param FunctionalTester $I
     */
    public function testCannotCreateTokenWithSameIdTwice(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->createToken([
            'roles' => [
                'upload.images'
            ],
            'data' => [],
            'id'   => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
        ], false);

        $I->createToken([
            'roles' => [
                'upload.images'
            ],
            'data' => [],
            'id'   => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
        ], false);

        $I->canSeeResponseContains('id_already_exists_please_select_other_one');
        $I->canSeeResponseCodeIs(400);
    }

    /**
     * Feature: Possibility to set "id" of a token manually
     * Case: Trying to enter non-uuid string
     *
     * @param FunctionalTester $I
     */
    public function testCannotCreateCustomTokenIfNotInUuidFormat(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->createToken([
            'roles' => [
                'upload.images'
            ],
            'data' => [],
            'id'   => 'international-workers-association'
        ], false);

        $I->canSeeResponseContains('id_expects_to_be_uuidv4_format');
        $I->canSeeResponseCodeIs(400);
    }

    /**
     * Feature: Possibility to set "id" of a token manually
     * Case: Cannot set "id" field, when does not have a proper role
     *
     * @param FunctionalTester $I
     */
    public function testNoRightsToUseIdFieldWhenGeneratingTokenWithoutSufficientPermissions(FunctionalTester $I): void
    {
        // create a limited token at first
        $I->amAdmin();
        $I->createToken([
            'roles' => [
                'security.generate_tokens',
            ],
            'data' => []
        ]);
        $I->storeIdAs('.token.id', 'LIMITED_TOKEN_NO_PREDICTABLE_IDS');

        // then use such token to test "access denied"
        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN_NO_PREDICTABLE_IDS'));
        $I->createToken([
            'roles' => [
                'upload.images'
            ],
            'data' => [],
            'id'   => 'international-workers-association'
        ], false);
        $I->canSeeResponseCodeIs(403);

        // then check that has permissions to create tokens at all, but without specifying "id"
        $I->amToken($I->getPreviouslyStoredIdOf('LIMITED_TOKEN_NO_PREDICTABLE_IDS'));
        $I->createToken([
            'roles' => [
                'upload.images'
            ],
            'data' => []
            // case: no "id" there
        ], false);
        $I->canSeeResponseCodeIs(201);
    }
}
