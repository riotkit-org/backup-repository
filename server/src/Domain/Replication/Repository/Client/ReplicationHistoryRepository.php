<?php declare(strict_types=1);

namespace App\Domain\Replication\Repository\Client;

use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Exception\DatabaseException;
use DateTimeImmutable;

interface ReplicationHistoryRepository
{
    public function findLastEntryTimestamp(): ?DateTimeImmutable;

    public function getErrorsCount(): int;

    /**
     * @param ReplicationLogEntry $entry
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public function wasEntryAlreadyFetched(ReplicationLogEntry $entry): bool;

    /**
     * @param ReplicationLogEntry $entry
     *
     * @throws DatabaseException
     */
    public function persist(ReplicationLogEntry $entry): void;

    /**
     * @throws DatabaseException
     */
    public function flush(): void;

    public function findByContentHash(string $getContentHash): ?ReplicationLogEntry;

    public function findNotFinishedTasksSince(\DateTime $since): array;
}
