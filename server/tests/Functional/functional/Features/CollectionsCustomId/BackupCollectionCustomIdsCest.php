<?php declare(strict_types=1);

namespace Tests\Functional\Features\CollectionsCustomId;

use FunctionalTester;
use Ramsey\Uuid\Uuid;

class BackupCollectionCustomIdsCest
{
    private function createValidCollection(FunctionalTester $I, string $generatedUuidForTest): string
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'lokatorzy.info.pl_database.tar.gz',
            'id'                => $generatedUuidForTest
        ]);

        return $generatedUuidForTest;
    }

    public function testAdministratorCanCreateACollectionWithSpecifiedId(FunctionalTester $I): void
    {
        $generatedUuidForTest = Uuid::uuid4()->toString();
        $this->createValidCollection($I, $generatedUuidForTest);

        $I->canSeeResponseCodeIsSuccessful();
        $I->canSeeResponseContainsJson(['collection' => ['id' => $generatedUuidForTest]]);
    }

    public function testCannotCreateCollectionWithNotValidUuid(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'lokatorzy.info.pl_database.tar.gz',
            'id'                => 'this-is-not-a-valid-uuid'
        ]);

        $I->canSeeResponseContains('custom_id_is_not_uuid_format');
        $I->canSeeResponseCodeIs(400);
    }

    public function testCantAssignCustomIdWhenPermissionNotGrantedOnToken(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->amToken(
            $I->createToken([
                'roles' => [
                    'collections.create_new'
                ]
            ])
        );
        $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'lokatorzy.info.pl_database.tar.gz',
            'id'                => 'this-is-not-a-valid-uuid'
        ]);

        $I->canSeeResponseCodeIs(403);
    }

    public function testCanAssignCustomIdWhenUserWasGrantedWithProperRole(FunctionalTester $I): void
    {
        $expectedId = Uuid::uuid4()->toString();

        $I->amAdmin();
        $I->amToken(
            $I->createToken([
                'roles' => [
                    'collections.create_new', 'collections.create_new.with_custom_id'
                ]
            ])
        );
        $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => '50MB',
            'maxCollectionSize' => '100MB',
            'strategy'          => 'delete_oldest_when_adding_new',
            'filename'          => 'lokatorzy.info.pl_database.tar.gz',
            'id'                => $expectedId
        ]);

        $I->canSeeResponseContainsJson(['collection' => ['id' => $expectedId]]);
        $I->canSeeResponseCodeIs(202);
    }

    public function testCannotCreateSameCollectionTwice(FunctionalTester $I): void
    {
        $generatedUuidForTest = Uuid::uuid4()->toString();
        $this->createValidCollection($I, $generatedUuidForTest);

        $I->canSeeResponseCodeIsSuccessful();

        // create the same collection again
        $this->createValidCollection($I, $generatedUuidForTest);

        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('id_not_unique');
    }
}
