<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

/**
 * @group Domain/Backup
 */
class BackupCollectionValidationCest
{
    public function testWillValidateFormatOfSubmittedFields(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => -5,
            'maxOneVersionSize' => 'invalid-format-cannot-parse',
            'maxCollectionSize' => '1111',
            'strategy'          => 'invalid_strategy',
            'filename'          => 'valid-filname.txt'
        ]);

        $I->canSeeResponseContainsJson([
            "fields" => [
                "maxBackupsCount" => [
                    "message" => "Number cannot be negative, got -5",
                    "code" => 42016
                ],
                "maxOneVersionSize" => [
                    "message" => "Disk space format parsing error",
                    "code" => 42010
                ],
                "strategy" => [
                    "message" => "Invalid collection strategy picked \"invalid_strategy\". Choices: delete_oldest_when_adding_new, alert_when_too_many_versions",
                    "code" => 42021
                ]
            ],
            "type" => "validation.error"
        ]);
        $I->canSeeResponseCodeIs(400);
    }

    public function testSingleElementCannotExceedWholeCollectionSize(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => "11MB",
            'maxCollectionSize' => '10MB',
            'strategy'          => 'alert_when_too_many_versions',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContainsJson([
            "error" => "Collection size cannot be smaller than single version size",
            "code"  => 40105,
            "type"  => "validation.error"
        ]);

        $I->canSeeResponseCodeIs(400);
    }

    public function testSingleElementIsBiggerThanGloballyDefined(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => "50GB",
            'maxCollectionSize' => '150GB',
            'strategy'          => 'alert_when_too_many_versions',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContainsJson([
            "error" => "Maximum file size of 25.00GB reached",
            "code"  => 40103,
            "type"  => "validation.error"
        ]);

        $I->canSeeResponseCodeIs(400);
    }

    public function testWholeCollectionSizeIsBiggerThanGloballyDefined(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => "5MB",
            'maxCollectionSize' => '150TB',
            'strategy'          => 'alert_when_too_many_versions',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContainsJson([
            "error" => "Maximum collection size cannot exceed 150.00GB",
            "code"  => 40104,
            "type"  => "validation.error"
        ]);

        $I->canSeeResponseCodeIs(400);
    }

    public function testMaxBackupsCountExceedsGloballyDefinedLimit(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 99999999,
            'maxOneVersionSize' => "5MB",
            'maxCollectionSize' => '100MB',
            'strategy'          => 'alert_when_too_many_versions',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContainsJson([
            "error" => "Maximum count of 10 files reached",
            "code"  => 40102,
            "type"  => "validation.error"
        ]);

        $I->canSeeResponseCodeIs(400);
    }

    public function testCollectionMaxSizeNotEnoughToHandleBackups(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 5,
            'maxOneVersionSize' => "5MB",
            'maxCollectionSize' => '5MB',
            'strategy'          => 'alert_when_too_many_versions',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContainsJson([
            "error" => "Collection maximum size is too small, requires at least 25.00MB",
            "code"  => 40106,
            "type"  => "validation.error"
        ]);

        $I->canSeeResponseCodeIs(400);
    }
}
