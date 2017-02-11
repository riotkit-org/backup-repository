<?php declare(strict_types=1);

namespace Tests\Seeders;

use Manager\Domain\TagManagerInterface;
use Manager\FileRegistry;
use Model\Entity\File;

/**
 * Provides entries for "File" entity
 * connected with "Tag"
 *
 * @package Tests\Seeders
 */
trait TaggedFileSeeder
{
    use BaseSeeder;

    /**
     * Creates a file "test.txt" tagged with "uploads"
     */
    protected function createTestTaggedFile()
    {
        $file = new File();
        $file->setMimeType('text/plain');
        $file->setContentHash(md5('test'));
        $file->setDateAdded(new \DateTime());
        $file->setFileName('test.txt');


        /** @var FileRegistry $manager */
        $manager = $this->getApp()->offsetGet('manager.file_registry');
        $manager->save($file);

        /** @var TagManagerInterface $tagManager */
        $tagManager = $this->getApp()->offsetGet('manager.tag');
        $tagManager->attachTagToFile('uploads', $file);
    }
}