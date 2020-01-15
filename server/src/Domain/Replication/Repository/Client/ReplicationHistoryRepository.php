<?php declare(strict_types=1);

namespace App\Domain\Replication\Repository\Client;

interface ReplicationHistoryRepository
{
    public function findLastEntryTimestamp(): ?\DateTime;

    public function wasEntryAlreadyFetched($entry): bool;

    public function add($entry): void;

    public function flush(): void;
}
