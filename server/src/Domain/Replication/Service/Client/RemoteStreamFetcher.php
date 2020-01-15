<?php declare(strict_types=1);

namespace App\Domain\Replication\Service\Client;

use App\Domain\Replication\DTO\File;

interface RemoteStreamFetcher
{
    /**
     * @param int $limit
     * @param \DateTime|null $since
     *
     * @return File[]
     */
    public function fetch(int $limit, ?\DateTime $since = null): array;
}
