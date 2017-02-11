<?php declare(strict_types=1);

namespace Tests\Manager;

use Manager\Domain\TagManagerInterface;
use Model\Entity\File;
use Model\Entity\Tag;
use Tests\WolnosciowiecTestCase;

/**
 * @see TagManager
 * @package Tests\Manager
 */
class TagManagerTest extends WolnosciowiecTestCase
{
    /**
     * @return File
     */
    private function createExampleFile()
    {
        $seed = rand(99, 99999) . microtime(true);

        $file = new File();
        $file->setDateAdded(new \DateTime());
        $file->setContentHash(md5($seed));
        $file->setFileName($seed . '.txt');
        $file->setMimeType('text/plain');

        return $file;
    }

    /**
     * @see TagManager::attachTagToFile()
     */
    public function testAttachTagToFile()
    {
        $this->prepareDatabase();

        $file = $this->createExampleFile();

        /**
         * @var TagManagerInterface $manager
         * @var Tag                 $tag
         */
        $manager = $this->app->offsetGet('manager.tag');
        $manager->attachTagToFile('article.cover', $file);

        $tag = $file->getTags()->first();

        $this->assertNotEmpty($file->getId());
        $this->assertSame('article.cover', $tag->getName());

        // case: attach same tag to two different files
        // make sure that the Tag object will be RE-USED
        $secondFile = $this->createExampleFile();
        $manager->attachTagToFile('article.cover', $secondFile);

        $this->assertSame(
            $tag->getId(),
            $secondFile->getTags()->first()->getId(),
            'It seems that the tag was duplicated what was not intended'
        );
    }
}
