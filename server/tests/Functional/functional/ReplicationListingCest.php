<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;
use Tests\Urls;

class ReplicationListingCest
{
    public function testAdminCanAccessReplication(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->sendGET(Urls::URL_REPLICATION);
        $I->canSeeResponseCodeIs(200);
    }

    public function testUserWithoutReplicationRoleCannotSeeReplicationList(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all']);
        $I->sendGET(Urls::URL_REPLICATION);
        $I->canSeeResponseCodeIs(403);
    }

    public function testUserWithReplicationRoleCanSeeReplicationList(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream']);
        $I->sendGET(Urls::URL_REPLICATION);
        $I->canSeeResponseCodeIs(200);
    }

    public function testCreateTokenWithReplicationPassword(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'replicationEncryptionMethod' => 'aes-128-cbc',
                'replicationEncryptionKey'    => 'Worlds-26-richest-people-control-as-much-as-poorest-50-percent'
            ]
        ]);
        $I->sendGET(Urls::URL_REPLICATION);
        $I->canSeeResponseCodeIs(200);
    }

    public function testEncryptionMethodValueValidation(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'replicationEncryptionMethod' => 'SOME',
                'replicationEncryptionKey'    => 'class-struggle36'
            ]
        ], false);
        $I->canSeeResponseContains('form.data.replicationEncryptionMethod');
        $I->canSeeResponseCodeIs(400);
    }
}
