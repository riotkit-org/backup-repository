<?php declare(strict_types=1);

namespace Repository\Domain;

use Model\Entity\File;

/**
 * @package Repository\Domain
 */
interface FileRepositoryInterface
{
    /**
     * @param string $name File name or URL address
     * @return File|null
     */
    public function fetchOneByName(string $name);

    /**
     * @param array  $tags
     * @param string $searchQuery
     * @param int    $limit
     * @param int    $offset
     *
     * @return File[]
     */
    public function findByQuery(array $tags, string $searchQuery = '', int $limit, int $offset): array;

    /**
     * @param string $hash
     * @return File
     */
    public function getFileByContentHash(string $hash);
}