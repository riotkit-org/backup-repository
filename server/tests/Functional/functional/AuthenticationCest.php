<?php declare(strict_types=1);

namespace Tests\Functional;

require_once __DIR__ . '/../Urls.php';

use FunctionalTester;
use Tests\Urls;

class AuthenticationCest
{
    public function generateBasicUserAccess(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',
            'roles' => [
                'upload.all', 'upload.enforce_no_password', 'upload.enforce_tags_selected_in_token'
            ],
            'data' => [
                'tags' => ['gallery']
            ]
        ], false);

        $I->canSeeResponseCodeIs(201);
        $I->storeIdAs('.token.id', 'BASIC_USER_ACCESS_ID');
    }

    public function verifyUserAccessWasGenerated(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->lookupUser($I->getPreviouslyStoredIdOf('BASIC_USER_ACCESS_ID'));
        $I->canSeeResponseContainsJson([
            'token' => [
                'id' => $I->getPreviouslyStoredIdOf('BASIC_USER_ACCESS_ID')
            ]
        ]);
    }

    public function verifyTokenCanBeFoundInSearchByOneOfItsRoles(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForUsers('upload.enforce_tags_selected_in_token', 1, 50);
        $I->canSeeResponseContains($I->getPreviouslyStoredIdOf('BASIC_USER_ACCESS_ID'));
        $I->canSeeResponseCodeIs(200);
    }

    public function generateTokenWithLimitToSelectedTagsAndMimes(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-1@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => [
                'upload.all'
            ],
            'data' => [
                'tags' => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 14579
            ]
        ], false);
        $I->canSeeResponseCodeIs(201);
        $I->storeIdAs('.token.id', 'LIMITED_USER_ACCESS_ID');
    }

    public function verifyTheLimitedUserAccess(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->lookupUser($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'));
        $I->canSeeResponseContainsJson([
            'token' => [
                'id' => $I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'),
                'active' => true,
                'roles'  => ['upload.all'],
                'data'   => [
                    'tags'               => ['user_uploads.u123', 'user_uploads'],
                    'maxAllowedFileSize' => 14579
                ]
            ]
        ]);
    }

    public function testValidationContainsInvalidRoleName(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-2@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => ['upload.all', 'is-this-working?'],
            'data' => [
                'tags' => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 100
            ]
        ], false);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('Please select valid roles');
    }

    public function testCannotCreateUsersWhenRoleDoesNotAllowCreatingOnes(FunctionalTester $I): void
    {
        $I->amUser($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'));
        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-3@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => ['upload.all'],
            'data' => [
                'tags' => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 100
            ]
        ], false);
        $I->canSeeResponseCodeIs(403);
        $I->canSeeResponseContains('Current access does not allow to create users');
    }

    public function testCannotLookupUsersWhenHaveNoRightsGrantedToLookupUsers(FunctionalTester $I): void
    {
        $I->amUser($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'));
        $I->lookupUser($I->getPreviouslyStoredIdOf('BASIC_USER_ACCESS_ID'));
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
        $I->amUser($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'));
        $I->sendGET(Urls::ROLES_LISTING);
        $I->canSeeResponseCodeIs(403);
    }

    public function testTryToCreateUsersAsNotAuthorizedUserToDeleteUsers(FunctionalTester $I): void
    {
        $I->amUser($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'));
        $I->revokeAccess($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'));
        $I->canSeeResponseCodeIs(403);

        $I->amUser($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'));
        $I->revokeAccess($I->getPreviouslyStoredIdOf('BASIC_USER_ACCESS_ID'));
        $I->canSeeResponseCodeIs(403);
    }

    public function testDeletePreviouslyCreatedUsers(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->revokeAccess($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID'));
        $I->canSeeResponseCodeIs(200);

        $I->revokeAccess($I->getPreviouslyStoredIdOf('BASIC_USER_ACCESS_ID'));
        $I->canSeeResponseCodeIs(200);
    }

    /**
     * Feature: Possibility to set "id" of a token manually
     * Case: Successful case
     *
     * @param FunctionalTester $I
     */
    public function testCreateUserWithCustomIdSpecifiedInRequest(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-4@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => [
                'upload.all'
            ],
            'data' => [],
            'id'   => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
        ], false);
        $I->canSeeResponseCodeIs(201);
    }

    /**
     * Feature: Possibility to set "id" of a user manually
     * Case: Trying to create same user at least twice
     *
     * @param FunctionalTester $I
     */
    public function testCannotCreateUserWithSameIdTwice(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-5@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => [
                'upload.all'
            ],
            'data' => [],
            'id'   => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
        ], false);

        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-6@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => [
                'upload.all'
            ],
            'data' => [],
            'id'   => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
        ], false);

        $I->canSeeResponseContains('User already exists');
        $I->canSeeResponseContains('"code": 40001');
        $I->canSeeResponseCodeIs(400);
    }

    /**
     * Case: Trying to register two users on same e-mail address. ID is not specified, will be generated.
     *
     * @param FunctionalTester $I
     */
    public function testCannotCreateTwoUsersSharingSameEmailAddress(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-5@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',
            'roles' => ['upload.all'],
            'data' => [],
        ], false);

        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-5@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => ['upload.all'],
            'data' => [],
        ], false);

        $I->canSeeResponseContains('User already exists');
        $I->canSeeResponseContains('"code": 40001');
        $I->canSeeResponseCodeIs(400);
    }

    /**
     * Case: E-mail and password fields are mandatory
     *
     * @param FunctionalTester $I
     */
    public function testEmailAndPasswordAreMandatoryFields(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->createUser([
            // password: missing
            // email: missing
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',
            'roles' => ['upload.all'],
            'data' => [],
        ], false);

        $I->canSeeResponseContains('Invalid e-mail format');
        $I->canSeeResponseContains('"code": 40005');

        $I->canSeeResponseContains('Password is too short');
        $I->canSeeResponseContains('"code": 40006');

        $I->canSeeResponseCodeIs(400);
    }

    /**
     * Feature: Possibility to set "id" of a user manually
     * Case: Trying to enter non-uuid string
     *
     * @param FunctionalTester $I
     */
    public function testCannotCreateCustomUserIfNotInUuidFormat(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-7@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => [
                'upload.all'
            ],
            'data' => [],
            'id'   => 'international-workers-association'
        ], false);

        $I->canSeeResponseContains('User ID format invalid, should be a uuidv4 format');
        $I->canSeeResponseContains('"code": 40021');
        $I->canSeeResponseCodeIs(400);
    }

    /**
     * Feature: Possibility to set "id" of a user manually
     * Case: Cannot set "id" field, when does not have a proper role
     *
     * @param FunctionalTester $I
     */
    public function testNoRightsToUseIdFieldWhenGeneratingUserWithoutSufficientPermissions(FunctionalTester $I): void
    {
        // create a limited token at first
        $I->amAdmin();
        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-8@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => [
                'security.generate_tokens',
            ],
            'data' => []
        ]);
        $I->storeIdAs('.token.id', 'LIMITED_USER_ACCESS_ID_NO_PREDICTABLE_IDS');

        // then use such access to test "access denied"
        $I->amUser($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID_NO_PREDICTABLE_IDS'));
        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-9@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => [
                'upload.all'
            ],
            'data' => [],
            'id'   => 'international-workers-association'
        ], false);
        $I->canSeeResponseCodeIs(403);

        // then check that has permissions to create user accounts at all, but without specifying "id"
        $I->amUser($I->getPreviouslyStoredIdOf('LIMITED_USER_ACCESS_ID_NO_PREDICTABLE_IDS'));
        $I->createUser([
            'password'     => 'anarchist-book-fair-1936',
            'email'        => 'example-10@riseup.net',
            'organization' => 'Wolna Biblioteka',
            'about'        => 'A libertarian library',

            'roles' => [
                'upload.all'
            ],
            'data' => []
            // case: no "id" there
        ], false);
        $I->canSeeResponseCodeIs(201);
    }

    public function listAllAvailableRoles(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->sendGET('/auth/roles');

        $I->canSeeResponseContainsJson([
            'data' => [
                'upload.all' => 'Allows to upload files at all',
            ]
        ]);
    }
}
