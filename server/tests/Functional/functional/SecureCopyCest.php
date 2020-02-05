<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;
use Tests\Urls;

class SecureCopyCest
{
    public function testAdminCanAccessReplication(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(200);
    }

    public function testUserWithoutReplicationRoleCannotSeeReplicationList(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all']);
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(403);
    }

    public function testUserWithReplicationRoleCanSeeReplicationList(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream']);
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(200);
    }

    public function testCreateTokenWithEncryptionPassword(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyEncryptionMethod' => 'aes-128-cbc',
                'secureCopyEncryptionKey'    => 'Worlds-26-richest-people-control-as-much-as-poorest-50-percent'
            ]
        ]);
        $I->receiveListOfElementsFromSecureCopy('file');
        $I->canSeeResponseCodeIs(200);
    }

    public function testEncryptionMethodValueValidation(FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'securecopy.stream'], [
            'data' => [
                'secureCopyEncryptionMethod' => 'SOME',
                'secureCopyEncryptionKey'    => 'class-struggle36'
            ]
        ], false);
        $I->canSeeResponseContains('form.data.secureCopyEncryptionMethod');
        $I->canSeeResponseCodeIs(400);
    }
}
