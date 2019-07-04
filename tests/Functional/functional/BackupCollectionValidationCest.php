<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

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

        $I->canSeeResponseContains('"maxBackupsCount":"number_cannot_be_negative_value"');
        $I->canSeeResponseContains('"maxOneVersionSize":"cannot_parse_disk_space_check_format"');
        $I->canSeeResponseContains('"unknown_strategy_allowed___delete_oldest_when_adding_new___or__alert_when_backup_limit_reached"');
        $I->canSeeResponseCodeIs(400);
    }

    public function testSingleElementCannotExceedWholeCollectionSize(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 4,
            'maxOneVersionSize' => "11MB",
            'maxCollectionSize' => '10MB',
            'strategy'          => 'alert_when_backup_limit_reached',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContains('max_collection_size_is_lower_than_single_element_size');
        $I->canSeeResponseCodeIs(400);
    }

    public function testSingleElementIsBiggerThanGloballyDefined(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => "50GB",
            'maxCollectionSize' => '150GB',
            'strategy'          => 'alert_when_backup_limit_reached',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContains('max_one_version_size_too_big');
        $I->canSeeResponseContains('"max":"4.00GB"');
        $I->canSeeResponseCodeIs(400);
    }

    public function testWholeCollectionSizeIsBiggerThanGloballyDefined(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 2,
            'maxOneVersionSize' => "5MB",
            'maxCollectionSize' => '150TB',
            'strategy'          => 'alert_when_backup_limit_reached',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContains('max_collection_size_too_big');
        $I->canSeeResponseContains('"max":"15.00GB"');
        $I->canSeeResponseCodeIs(400);
    }

    public function testMaxBackupsCountExceedsGloballyDefinedLimit(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 99999999,
            'maxOneVersionSize' => "5MB",
            'maxCollectionSize' => '100MB',
            'strategy'          => 'alert_when_backup_limit_reached',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContains('max_backups_count_too_many');
        $I->canSeeResponseContains('"max":5');
        $I->canSeeResponseCodeIs(400);
    }

    public function testCollectionMaxSizeNotEnoughToHandleBackups(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createCollection([
            'maxBackupsCount'   => 5,
            'maxOneVersionSize' => "5MB",
            'maxCollectionSize' => '5MB',
            'strategy'          => 'alert_when_backup_limit_reached',
            'description'       => 'https://zsp.net.pl | https://iwa-ait.org',
            'filename'          => 'zsp-net-pl.sql.gz'
        ]);

        $I->canSeeResponseContains('max_collection_size_will_have_not_enough_space_to_keep_max_number_of_items');
        $I->canSeeResponseContains('"needsAtLeastValue":"25.00MB"');
        $I->canSeeResponseCodeIs(400);
    }
}
