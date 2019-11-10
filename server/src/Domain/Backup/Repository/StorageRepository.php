<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Entity\StoredFile;

interface StorageRepository
{
    public function findById($id): ?StoredFile;

    public function flushAll(): void;
}
