<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

/**
 * FEATURE: GIVEN the token is assigned a restriction role, THEN only one file can be uploaded using this token
 *
 * @group Domain/Backup
 * @group Security
 */
class FeatureOnlyOneFileAllowedToUploadCest
{
    public function testOnlyOneFileCanBeUploaded(\FunctionalTester $I): void
    {
        // create a collection first
        $I->amAdmin();
        $collection = $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => '5KB',
            'maxCollectionSize' => '1024KB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'description'       => 'Confederação Operária Brasileira database #2',
            'filename'          => 'cob_database-2.tar.gz'
        ]);

        $user = $I->createStandardUser(['roles' => ['upload.all', 'upload.only_once_successful', 'collections.upload_to_allowed_collections']]);
        $I->grantUserAccessToCollection($collection, $user->id);

        // switch to limited user
        $I->amUser($user->email, $user->password);
        $I->stopFollowingRedirects();

        // upload first time
        $I->uploadToCollection(
            $collection,
            "ZSP-IWA calls for a week of protest action against the repression of workers from the Post Office in Poland"
        );
        $I->canSeeResponseCodeIsSuccessful();

        // upload second time - should be an error
        $I->uploadToCollection(
            $collection,
            "ZSP-IWA calls for a week of protest action against the repression of workers from the Post Office in Poland"
        );

        $I->canSeeResponseCodeIs(401);
    }
}
