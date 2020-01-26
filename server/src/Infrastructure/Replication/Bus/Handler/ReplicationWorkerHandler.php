<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Bus\Handler;

use App\Domain\Replication\ActionHandler\Client\ProcessingWorkerActionHandler;
use App\Domain\Replication\Repository\Client\ReplicationHistoryRepository;
use App\Infrastructure\Replication\Bus\Message\ReplicationObjectMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ReplicationWorkerHandler implements MessageHandlerInterface
{
    private ReplicationHistoryRepository $repository;
    private ProcessingWorkerActionHandler                  $manager;

    public function __construct(ReplicationHistoryRepository $repository, ProcessingWorkerActionHandler $manager)
    {
        $this->repository = $repository;
        $this->manager    = $manager;
    }

    public function __invoke(ReplicationObjectMessage $message)
    {
        $log = $this->repository->findByContentHash($message->getContentHash());

        if (!$log) {
            // @todo: Add exception
            throw new \Exception('Replication log entry is no longer valid!');
        }

        $this->manager->handle($log);
    }

    public static function getHandledMessages(): iterable
    {
        yield ReplicationObjectMessage::class;
    }
}
