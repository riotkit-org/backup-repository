<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Manager;

use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Manager\Client\QueueManager;
use App\Infrastructure\Replication\Bus\Message\ReplicationObjectMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class SymfonyMessengerQueueManagerImplementation implements QueueManager
{
    private MessageBusInterface $messenger;

    public function __construct(MessageBusInterface $bus)
    {
        $this->messenger = $bus;
    }

    public function put(ReplicationLogEntry $entry): void
    {
        $this->messenger->dispatch(new ReplicationObjectMessage($entry));
    }
}
