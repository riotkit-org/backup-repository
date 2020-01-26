<?php declare(strict_types=1);

namespace App\Domain\Replication\Service\Client;

use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Exception\ReplicationException;
use DateTimeImmutable;

interface RemoteStreamProvider
{
    /**
     * @param int                    $limit
     * @param DateTimeImmutable|null $since
     * @param bool                   $test
     *
     * @return ReplicationLogEntry[]
     *
     * @throws ReplicationException
     */
    public function fetch(int $limit, ?DateTimeImmutable $since = null, bool $test = false): array;

    /**
     * @param int $limit
     * @param DateTimeImmutable|null $since
     * @param bool $showExampleData
     *
     * @return array
     *
     * @throws ReplicationException
     */
    public function fetchRaw(int $limit, ?DateTimeImmutable $since = null, bool $showExampleData = false): array;
}
