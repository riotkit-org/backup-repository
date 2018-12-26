<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Collection\VersionsCollection;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;

interface VersionRepository
{
    /**
     * @param BackupCollection $collection
     *
     * @return VersionsCollection
     */
    public function findCollectionVersions(BackupCollection $collection): VersionsCollection;

    /**
     * @param StoredVersion $version
     */
    public function persist(StoredVersion $version): void;

    /**
     * @param StoredVersion $version
     */
    public function delete(StoredVersion $version): void;

    /**
     * @param StoredVersion $version
     */
    public function flush(StoredVersion $version): void;

    /**
     * Send all pending changes to the database/storage
     */
    public function flushAll(): void;
}
