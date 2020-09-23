<?php declare(strict_types=1);

namespace Tests\Functional\Features\FileAttributes;

class StorageAttributesCest
{
    public function testUploadFileWithCorrectAttributes(\FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all']);

        $I->uploadByPayload(str_repeat('Power to the people.', 16), [
            'fileName' => 'democracy-fundamentals.txt',
            'attributes' => json_encode([
                'bahub.iv' => '123456789'
            ])
        ]);
        $I->storeIdAs('.filename', 'FILENAME');

        // verification
        $I->readFileAttributes($I->getPreviouslyStoredIdOf('FILENAME'));
    }
}
