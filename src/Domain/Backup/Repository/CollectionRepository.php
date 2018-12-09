<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Entity\BackupCollection;

interface CollectionRepository
{
    /**
     * Re-connect the entity with database and entity manager
     * (after eg. it was CLONED)
     *
     * @param BackupCollection $collection
     *
     * @return BackupCollection
     */
    public function merge(BackupCollection $collection): ?BackupCollection;

    /**
     * @param BackupCollection $collection
     */
    public function persist(BackupCollection $collection): void;

    /**
     * Send all pending changes to the database/storage
     */
    public function flushAll(): void;
}
