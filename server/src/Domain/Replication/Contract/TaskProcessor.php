<?php declare(strict_types=1);

namespace App\Domain\Replication\Contract;

use App\Domain\Replication\Entity\ReplicationLogEntry;

interface TaskProcessor
{
    public function process(ReplicationLogEntry $message);

    public function canProcess(ReplicationLogEntry $message): bool;
}
