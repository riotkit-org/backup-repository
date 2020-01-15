<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Repository\Client;

use App\Domain\Replication\Repository\Client\ReplicationHistoryRepository;

class DoctrineReplicationHistoryRepository implements ReplicationHistoryRepository
{
    public function findLastEntryTimestamp(): ?\DateTime
    {
        return new \DateTime();
    }

    public function wasEntryAlreadyFetched($entry): bool
    {
        return false;
    }

    public function add($entry): void
    {
    }

    public function flush(): void
    {
    }
}
