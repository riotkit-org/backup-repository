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
     * @param string $hash
     * @return File
     */
    public function getFileByContentHash(string $hash);
}