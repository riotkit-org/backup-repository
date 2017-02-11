<?php declare(strict_types=1);

namespace Factory;

use Factory\Domain\TagFactoryInterface;
use Model\Entity\Tag;

/**
 * Constructs objects of Tag type
 * ------------------------------
 *
 * @package Factory
 */
class TagFactory implements TagFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createTag(string $tagName): Tag
    {
        return (new Tag())
            ->setName($tagName)
            ->setDateAdded(new \DateTime());
    }
}
