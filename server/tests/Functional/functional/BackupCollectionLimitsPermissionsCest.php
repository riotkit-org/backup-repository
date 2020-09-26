<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

class BackupCollectionLimitsPermissionsCest
{
    public function testNotAllowedToCreateInfiniteCollections(FunctionalTester $I): void
    {
        $I->haveRoles(['collections.create_new'], [
            'data' => [
                'tags'               => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 14579
            ]
        ]);

        // attempt to create an infinite collection
        $this->createInfiniteCollection($I);
        $I->canSeeResponseCodeIs(403);
    }

    public function testUserCanCreateANormalCollection(FunctionalTester $I): void
    {
        $I->haveRoles(['collections.create_new'], [
            'data' => [
                'tags'               => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 14579
            ]
        ]);

        // attempt to create a normal collection
        $I->createCollection([
            'maxBackupsCount'   => 5,
            'maxOneVersionSize' => '1MB',
            'maxCollectionSize' => '5MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'solfed.org.uk_database.tar.gz'
        ]);
        $I->canSeeResponseCodeIsSuccessful();
    }

    public function testAdminCanCreateInfiniteCollections(FunctionalTester $I): void
    {
        $I->amAdmin();
        $this->createInfiniteCollection($I);
        $I->canSeeResponseCodeIsSuccessful();
    }

    public function testUserCanBeGrantedToCreateAInfiniteCollection(FunctionalTester $I): void
    {
        $I->haveRoles(['collections.create_new', 'collections.allow_infinite_limits'], [
            'data' => [
                'tags'               => ['user_uploads.u123', 'user_uploads'],
                'maxAllowedFileSize' => 14579
            ]
        ]);

        $this->createInfiniteCollection($I);
        $I->canSeeResponseCodeIsSuccessful();
    }

    private function createInfiniteCollection(FunctionalTester $I): void
    {
        $I->createCollection([
            'maxBackupsCount'   => 5,
            'maxOneVersionSize' => 0,
            'maxCollectionSize' => '5MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'solfed.org.uk_database.tar.gz'
        ]);
    }
}
