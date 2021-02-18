<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;
use Ramsey\Uuid\Uuid;

/**
 * @group Domain/Backup
 */
class BackupCollectionAccessControlCest
{
    /**
     * Check that "collections.manage_users_in_allowed_collections" permission is required to grant other users access to a collection
     *
     * @group Security
     *
     * @param FunctionalTester $I
     */
    public function testUserCantGrantAnybodyToCollectionWhenNoRightsToGrantAreOnTheOperationalUser(FunctionalTester $I): void
    {
        $I->amAdmin();
        $secondUser = $I->createStandardUser(['roles' => ['upload.all']]);
        $thirdUser = $I->createStandardUser(['roles' => ['upload.all']]);

        $I->amCollectionManager();
        $collectionId = $this->createExampleCollection($I);
        // give second user only access to uploading files, nothing more - "collections.manage_users_in_allowed_collections" is missing there for purpose
        $I->grantUserAccessToCollection($collectionId, $secondUser->id, ['collections.upload_to_allowed_collections']);

        // will fail because $secondUser does not have "collections.manage_users_in_allowed_collections" that would
        // allow him/her to grant $thirdUser to access this collection

        // Shortly: $secondUser has no rights to add next people to collection.
        $I->amUser($secondUser->email, $secondUser->password); // re-log as second user
        $I->grantUserAccessToCollection($collectionId, $thirdUser->id, ['collections.upload_to_allowed_collections']);
        $I->canSeeResponseCannotGrantAccessToCollection();
    }

    /**
     * Verify that user who grants rights to other user cannot grant rights that it does not have on its own
     *
     * @group Security
     *
     * @param FunctionalTester $I
     */
    public function testUserCannotGrantMoreThanHave(FunctionalTester $I): void
    {
        $I->amAdmin();
        $secondUser = $I->createStandardUser(['roles' => ['upload.all']]);
        $thirdUser = $I->createStandardUser(['roles' => ['upload.all']]);

        $I->amCollectionManager();
        $collectionId = $this->createExampleCollection($I);
        // this time $secondUser has rights to assign other user to collection
        $I->grantUserAccessToCollection($collectionId, $secondUser->id, [
            'collections.upload_to_allowed_collections',
            'collections.manage_users_in_allowed_collections'
        ]);

        // verify: We cannot assign a role that we do not have as $secondUser
        $I->amUser($secondUser->email, $secondUser->password); // re-log as second user
        $I->grantUserAccessToCollection($collectionId, $thirdUser->id, ['collections.list_versions_for_allowed_collections']);
        $I->canSeeResponseCannotGrantTooMuchAccessThanWeHave();
    }

    /**
     * Verify that user can have modified roles that were previously granted
     *
     * @param FunctionalTester $I
     */
    public function testGrantedRolesCanBeModified(FunctionalTester $I): void
    {
        $I->amAdmin();
        $secondUser = $I->createStandardUser(['roles' => ['upload.all']]);

        $I->amCollectionManager();
        $collectionId = $this->createExampleCollection($I);

        // assign two roles
        $I->grantUserAccessToCollection($collectionId, $secondUser->id, [
            'collections.upload_to_allowed_collections',
            'collections.manage_users_in_allowed_collections'
        ]);
        $I->canSeeResponseOfGrantedAccessIsSuccessful();

        // then assign three roles
        // @todo: IMPLEMENT PUT METHOD!
        $I->grantUserAccessToCollection($collectionId, $secondUser->id, [
            'collections.upload_to_allowed_collections',
            'collections.manage_users_in_allowed_collections',
            'collections.list_versions_for_allowed_collections'
        ]);
        $I->canSeeResponseOfGrantedAccessIsSuccessful();

        //
        // verify that user can use added role 'collections.list_versions_for_allowed_collections'
        //
        $I->amUser($secondUser->email, $secondUser->password);
        $I->browseCollectionVersions($collectionId);
        $I->canSeeResponseOfICanBrowseCollectionVersions();
    }

    public function testGrantingAndDenyingATokenToCollection(FunctionalTester $I): void
    {
        $I->amAdmin();
        $secondUser = $I->createStandardUser([
            'roles' => ['upload.all'],
            'data' => [
                'tags' => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 2049
            ]
        ]);

        $I->amCollectionManager();
        $collectionId = $this->createExampleCollection($I);

        $I->grantUserAccessToCollection($collectionId, $secondUser->id, ['collections.upload_to_allowed_collections']);
        $I->canSeeResponseOfGrantedAccessIsSuccessful();

        // verify that we can upload
        $this->uploadAsUser($I, $secondUser, $collectionId);
        $I->canSeeResponseOfUploadingToCollectionIsSuccessful();

        // revoke access
        $I->amAdmin();
        $I->revokeAccessToCollection($collectionId, $secondUser->id);
        $I->canSeeResponseOfRevokedAccessIsSuccessful();

        // verify that the access was revoked
        $this->uploadAsUser($I, $secondUser, $collectionId);
        $I->canSeeResponseCannotUploadToCollection();
    }

    protected function uploadAsUser(FunctionalTester $I, \User $user, string $collectionId)
    {
        $I->amUser($user->email, $user->password);
        $I->uploadToCollection($collectionId,
            "During this International Week we want to remind that we workers have our own weapons to protect ourselves from employers. 
            " . Uuid::uuid4()->getHex()
        );
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

        $I->amCollectionManager();

        $firstCollectionWhereUserHasPermissions = $this->createExampleCollection($I);
        $secondCollectionWhereAreNoPermissions = $this->createExampleCollection($I);

        /*
         * Grant user access to collections - difference is in assigned roles
         */

        // in first collection we will have rights to upload and list versions
        $I->grantUserAccessToCollection($firstCollectionWhereUserHasPermissions, $secondUser->id, [
            'collections.upload_to_allowed_collections',
            'collections.list_versions_for_allowed_collections'
        ]);

        // in second collection we will have no any permissions
        $I->grantUserAccessToCollection($secondCollectionWhereAreNoPermissions, $secondUser->id);

        /*
         * Tests - checking if permissions are there
         */

        // at first: become our user that was assigned to collections with different roles
        $I->amUser($secondUser->email, $secondUser->password);

        // test 1: attempt to upload, when globally there are no permissions, but specific per-collection were assigned to upload and list versions
        $I->uploadToCollection($firstCollectionWhereUserHasPermissions,
            "During this International Week we want to remind that we workers have our own weapons to protect ourselves from employers.");

        $I->canSeeResponseOfUploadingToCollectionIsSuccessful();

        // test 2: attempt to upload to collection, where user has no global nor specific rights
        $I->uploadToCollection($secondCollectionWhereAreNoPermissions,
            'Let’s imagine that you are working without a contract, how would you prove that you worked for a certain employer and they must pay you?');
        $I->canSeeResponseCannotUploadToCollection();
    }

    /**
     * Verify that we cannot set roles that are not related to collection usage, when granting a user access to a collection
     *
     * @param FunctionalTester $I
     */
    public function testNonCollectionRolesShouldNotBePossibleToSelect(FunctionalTester $I): void
    {
        $I->amAdmin();
        $secondUser = $I->createStandardUser(['roles' => ['upload.all']]);

        $I->amCollectionManager();
        $collectionId = $this->createExampleCollection($I);

        $I->grantUserAccessToCollection($collectionId, $secondUser->id, [
            'security.create_unlimited_accounts',
        ]);

        $I->canSeeResponseCodeIsClientError();
        $I->canSeeErrorResponse('Invalid role selected', 40010, 'validation.error');
    }

    private function createExampleCollection(FunctionalTester $I): string
    {
        $id = $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'zsp.net.pl_database.tar.gz'
        ]);

        $I->canSeeResponseCodeIsSuccessful();

        return $id;
    }
}
