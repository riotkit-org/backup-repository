<?php declare(strict_types=1);

namespace Repository\Domain;

use Model\Entity\Tag;

/**
 * @package Repository\Domain
 */
interface TagRepositoryInterface
{
    /**
     * @param string $tagName
     * @return Tag|null
     */
    public function findOneByName(string $tagName);
}
