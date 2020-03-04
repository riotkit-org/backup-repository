<?php declare(strict_types=1);

namespace Tests\Functional\Features\StorageDeduplication;

use FunctionalTester;

/**
 * FEATURE: Be able to submit a file with invalid filename - File Repository should be able to correct that name
 */
class StripFilenamesCest
{
    // @todo: Edge cases such as - only wrong characters (empty filename or too short filename)

    public function submitFileWithInvalidFilenameWillBeSuccessWithFilenameStrippingTurnedOn(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->uploadByPayload(str_repeat('International Workers Association', 1024), [
            'fileName'               => 'this name contains invalid? characters: ***',
            'public'                 => true,
            'stripInvalidCharacters' => true,
        ]);

        $I->canSeeResponseContains('thisnamecontainsinvalidcharacters');
        $I->canSeeResponseCodeIs(200);
    }

    public function submitFileWithStrippingTurnedOffAndSeeThatValidationErrorWillBeThrown(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->uploadByPayload(str_repeat('International Workers Association', 1024), [
            'fileName'               => 'this name contains invalid? characters: ***',
            'public'                 => true,
            'stripInvalidCharacters' => false,
        ]);

        $I->canSeeResponseCodeIs(400);
    }

    public function checkThatValidationErrorWillHappenIfAfterStrippingTheFilenameLengthWillBeTooShort(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->uploadByPayload(str_repeat('International Workers Association', 1024), [
            'fileName'               => '??????',
            'public'                 => true,
            'stripInvalidCharacters' => true, // by default true
        ]);

        $I->canSeeResponseContains('The filename is empty');
        $I->canSeeResponseCodeIs(400);
    }
}
