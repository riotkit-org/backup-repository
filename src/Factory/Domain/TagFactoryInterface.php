<?php declare(strict_types=1);

namespace Factory\Domain;

use Model\Entity\Tag;

/**
 * @package Factory\Domain
 */
interface TagFactoryInterface
{
    /**
     * Create a new Tag object
     *
     * @param string $tagName
     * @return Tag
     */
    public function createTag(string $tagName): Tag;
}