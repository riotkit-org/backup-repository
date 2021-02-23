<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

/**
 * FEATURE: GIVEN the access token is assigned a restriction role, THEN only one file can be uploaded using this token
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

        $user = $I->createStandardUser(['roles' => ['upload.all', 'collections.upload_to_allowed_collections']]);
        $I->grantUserAccessToCollection($collection, $user->id);

        // create an access token with additional restriction "upload.only_once_successful"
        $accessToken = $I->createAccessToken(['upload.all', 'collections.upload_to_allowed_collections', 'upload.only_once_successful']);

        // switch to limited user
        $I->iHaveToken($accessToken->jwtSecret);
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

        $I->canSeeResponseCodeIs(403);
    }
}
