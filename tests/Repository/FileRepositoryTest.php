<?php declare(strict_types=1);

namespace Tests\Repository;

use Manager\FileRegistry;
use Repository\Domain\FileRepositoryInterface;
use Tests\WolnosciowiecTestCase;

/**
 * @see FileRepositoryInterface
 * @package Tests\Repository
 */
class FileRepositoryTest extends WolnosciowiecTestCase
{
    /**
     * @return FileRepositoryInterface
     */
    private function getRepository()
    {
        return $this->app->offsetGet('repository.file');
    }

    /**
     * @see FileRepositoryInterface::fetchOneByName()
     * @see FileRepositoryInterface::getFileByContentHash()
     */
    public function testFetchOne()
    {
        $this->prepareDatabase();

        /** @var FileRegistry $fileManager */
        $fileManager = $this->app->offsetGet('manager.file_registry');
        file_put_contents(__DIR__ . '/../../web/storage/6f297f45-phpunit-test.txt', 'Hello world');

        // register the file in the registry
        $file = $fileManager->registerByName('phpunit-test.txt', 'text/plain');

        // fetch back from the registry by name
        $fetchedFile = $this->getRepository()->fetchOneByName($file->getFileName());
        $this->assertSame($fetchedFile->getId(), $file->getId());

        // fetch by content hash
        $fetchedFile = $this->getRepository()->getFileByContentHash($file->getContentHash());
        $this->assertSame($fetchedFile->getId(), $file->getId());

        @unlink(__DIR__ . '/../../web/storage/6f297f45-phpunit-test.txt');
    }
}
