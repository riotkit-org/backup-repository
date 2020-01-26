<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Bus\Message;

use App\Domain\Replication\Entity\ReplicationLogEntry;

class ReplicationObjectMessage
{
    private string $contentHash;

    public function __construct(ReplicationLogEntry $entry)
    {
        $this->contentHash = $entry->getContentHash();
    }

    public function getContentHash(): string
    {
        return $this->contentHash;
    }
}
