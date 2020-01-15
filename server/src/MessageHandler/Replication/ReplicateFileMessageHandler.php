<?php declare(strict_types=1);

namespace App\MessageHandler\Replication;

use App\Domain\Replication\Message\ReplicateFileMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ReplicateFileMessageHandler implements MessageHandlerInterface
{
    public function __invoke(ReplicateFileMessage $message)
    {
        dump($message->getFileName());
    }
}
