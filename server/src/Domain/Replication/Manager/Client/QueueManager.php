<?php declare(strict_types=1);

namespace App\Domain\Replication\Manager\Client;

use App\Domain\Replication\Entity\ReplicationLogEntry;

interface QueueManager
{
    public function put(ReplicationLogEntry $entry): void;
}
