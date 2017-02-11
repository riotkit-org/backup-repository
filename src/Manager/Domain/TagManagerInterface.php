<?php declare(strict_types=1);

namespace Manager\Domain;

use Model\Entity\File;
use Model\Entity\Tag;

/**
 * @package Manager\Domain
 */
interface TagManagerInterface
{
    /**
     * @param string $tagName
     * @param File   $file
     */
    public function attachTagToFile(string $tagName, File $file);

    /**
     * Normalize tag name, strip out unnecessary characters
     *
     * @param string $tagName
     * @return string
     */
    public function getNormalizedName(string $tagName): string;

    /**
     * @inheritdoc
     */
    public function save(Tag $tag);
}