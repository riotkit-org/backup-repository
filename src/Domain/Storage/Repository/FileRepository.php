<?php declare(strict_types=1);

namespace App\Domain\Storage\Repository;

use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\ValueObject\Checksum;
use App\Domain\Storage\ValueObject\Filename;

interface FileRepository
{
    /**
     * Find a file in the registry by it's name
     *
     * @param Filename $filename
     *
     * @return StoredFile|null
     */
    public function findByName(Filename $filename): ?StoredFile;

    /**
     * Find a file by it's content (matching the checksum)
     *
     * @param Checksum $checksum
     *
     * @return StoredFile|null
     */
    public function findByHash(Checksum $checksum): ?StoredFile;

    /**
     * @param StoredFile $file
     */
    public function persist(StoredFile $file): void;

    /**
     * @param null|StoredFile|StoredFile[] $files
     */
    public function flush($files = null): void;

    /**
     * @param StoredFile $file
     */
    public function delete(StoredFile $file): void;
}
