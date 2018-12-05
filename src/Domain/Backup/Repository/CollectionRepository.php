<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Entity\BackupCollection;

interface CollectionRepository
{
    /**
     * @param BackupCollection $collection
     */
    public function persist(BackupCollection $collection): void;

    /**
     * Send all pending changes to the database/storage
     */
    public function flushAll(): void;
}
