<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler\Client;

use App\Domain\Replication\ActionHandler\BaseReplicationHandler;
use App\Domain\Replication\Manager\Client\QueueManager;
use App\Domain\Replication\Service\Client\RemoteStreamProvider;
use App\Domain\Replication\Repository\Client\ReplicationHistoryRepository;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

/**
 * Downloads metadata stream from PRIMARY server and applies to local queue, so the workers
 * could pick and process download of single elements
 *
 * QUEUE MESSAGE SENDER
 */
class ReplicationStreamFetchingActionHandler extends BaseReplicationHandler
{
    private RemoteStreamProvider $streamProvider;
    private ReplicationHistoryRepository $repository;
    private QueueManager $queueManager;

    public function __construct(RemoteStreamProvider $fetcher, ReplicationHistoryRepository $repository,
                                LoggerInterface $logger, QueueManager $queueManager)
    {
        $this->streamProvider = $fetcher;
        $this->repository     = $repository;
        $this->logger         = $logger;
        $this->queueManager   = $queueManager;
    }

    public function handle(?DateTimeImmutable $startingPoint): void
    {
        $this->enqueueMissingElements();
        $this->fetchNewElements($startingPoint, 100);
    }

    private function enqueueMissingElements(): void
    {
        $since = (new \DateTime())->modify('-24 hours');
        $tasks = $this->repository->findNotFinishedTasksSince($since);

        $this->log('Found ' . count($tasks) . ' tasks that requires re-trigger');

        foreach ($tasks as $entry) {
            $this->queueManager->put($entry);
        }
    }

    private function fetchNewElements(?DateTimeImmutable $startingPoint, int $limit = 100): void
    {
        $lastTimestamp = $startingPoint ?: $this->repository->findLastEntryTimestamp();

        foreach ($this->streamProvider->fetch($limit, $lastTimestamp, false) as $entry) {
            if ($this->repository->wasEntryAlreadyFetched($entry)) {
                $this->log('.. Skipping "' . $entry . '", already present');
                continue;
            }

            $this->log('.. Persisting "' . $entry . '"');
            $this->repository->persist($entry);
            $this->queueManager->put($entry);
        }

        $this->log('Flushing collected entries');
        $this->repository->flush();
    }
}
