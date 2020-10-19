<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

/**
 * @group Domain/Backup
 */
class BackupCollectionAccessControlCest
{
    public function testGrantingAndDenyingATokenToCollection(FunctionalTester $I): void
    {
        $I->amAdmin();
        $secondUser = $I->createStandardUser([
            'roles' => ['upload.all'],
            'data' => [
                'tags' => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 100
            ]
        ]);
        $I->haveRoles(['collections.create_new', 'collections.manage_tokens_in_allowed_collections']);

        $collectionId = $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'zsp.net.pl_database.tar.gz'
        ]);

        $I->grantTokenAccessToCollection($collectionId, $secondUser->id);
        $I->canSeeResponseCodeIsSuccessful();

        $I->revokeAccessToCollection($collectionId, $secondUser->id);
        $I->canSeeResponseCodeIsSuccessful();

        // @todo: Check that access was really revoked
    }

    /**
     * Feature: I can assign specific roles for given user
     *
     * @param FunctionalTester $I
     */
    public function testGrantingUserPermissionsPerCollectionAllowsToSelectRolesPerCollection(FunctionalTester $I): void
    {
        $I->amAdmin();
        $secondUser = $I->createStandardUser(['roles' => ['upload.all']]);

        $I->haveRoles(['collections.create_new', 'collections.manage_tokens_in_allowed_collections']);

        $firstCollectionWhereUserHasPermissions = $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'zsp.net.pl_database.tar.gz'
        ]);

        $secondCollectionWhereAreNoPermissions = $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'zsp.net.pl_database.tar.gz'
        ]);

        /*
         * Grant user access to collections - difference is in assigned roles
         */

        // in first collection we will have rights to upload and list versions
        $I->grantTokenAccessToCollection($firstCollectionWhereUserHasPermissions, $secondUser->id, [
            'collections.upload_to_allowed_collections',
            'collections.list_versions_for_allowed_collections'
        ]);

        // in second collection we will have no any permissions
        $I->grantTokenAccessToCollection($secondCollectionWhereAreNoPermissions, $secondUser->id);

        /*
         * Tests - checking if permissions are there
         */

        // at first: become our user that was assigned to collections with different roles
        $I->amUser($secondUser->email, $secondUser->password);

        // test 1: attempt to upload, when globally there are no permissions, but specific per-collection were assigned to upload and list versions
        $I->uploadToCollection($firstCollectionWhereUserHasPermissions,
            "During this International Week we want to remind that we workers have our own weapons to protect ourselves from employers.");

        $I->canSeeResponseCodeIsSuccessful();

        // test 2: attempt to upload to collection, where user has no global nor specific rights
        $I->uploadToCollection($secondCollectionWhereAreNoPermissions,
            'Let’s imagine that you are working without a contract, how would you prove that you worked for a certain employer and they must pay you?');

        $I->canSeeResponseCodeIsClientError();
        $I->canSeeResponseContainsJson([
            "error" => "Current access does not grant you a possibility to upload to this backup collection",
            "code" => 40308,
            "type" => "request.auth-error"
        ]);
    }

    // @todo: Test - assigning non-collection roles in collection user access granting request
}
