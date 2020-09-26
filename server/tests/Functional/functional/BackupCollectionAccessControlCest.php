<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

class BackupCollectionAccessControlCest
{
    public function testGrantingAndDenyingATokenToCollection(FunctionalTester $I): void
    {
        $I->amAdmin();
        $secondTokenId = $I->createToken([
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

        $I->grantTokenAccessToCollection($collectionId, $secondTokenId);
        $I->canSeeResponseCodeIsSuccessful();

        $I->revokeAccessToCollection($collectionId, $secondTokenId);
        $I->canSeeResponseCodeIsSuccessful();
    }
}
