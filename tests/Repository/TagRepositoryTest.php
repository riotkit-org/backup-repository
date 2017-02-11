<?php declare(strict_types=1);

namespace Tests\Repository;

use Model\Entity\Tag;
use Repository\Domain\TagRepositoryInterface;
use Tests\Seeders\TaggedFileSeeder;
use Tests\WolnosciowiecTestCase;

/**
 * @see TagRepository
 * @package Tests\Repository
 */
class TagRepositoryTest extends WolnosciowiecTestCase
{
    use TaggedFileSeeder;

    /**
     * @return TagRepositoryInterface
     */
    private function getRepository(): TagRepositoryInterface
    {
        return $this->getApp()->offsetGet('repository.tag');
    }

    /**
     * @see TagRepository::findOneByName()
     */
    public function testFindOneByName()
    {
        $this->prepareDatabase();
        $this->createTestTaggedFile();

        $tag = $this->getRepository()->findOneByName('uploads');
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertSame('uploads', $tag->getName());
        $this->assertNotEmpty($tag->getId());
        $this->assertNull($this->getRepository()->findOneByName('non-existing-tag-name'));
    }
}
