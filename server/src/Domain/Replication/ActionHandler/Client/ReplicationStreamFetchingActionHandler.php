<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler\Client;

use App\Domain\Replication\ActionHandler\BaseReplicationHandler;
use App\Domain\Replication\Service\Client\RemoteStreamFetcher;
use App\Domain\Replication\Repository\Client\ReplicationHistoryRepository;
use Psr\Log\LoggerInterface;

class ReplicationStreamFetchingActionHandler extends BaseReplicationHandler
{
    /**
     * @var RemoteStreamFetcher
     */
    private $fetcher;

    /**
     * @var ReplicationHistoryRepository
     */
    private $persister;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(RemoteStreamFetcher $fetcher, ReplicationHistoryRepository $persister, LoggerInterface $logger)
    {
        $this->fetcher   = $fetcher;
        $this->persister = $persister;
        $this->limit     = 100;
        $this->logger    = $logger;
    }

    public function handle(): void
    {
        $lastTimestamp = $this->persister->findLastEntryTimestamp();

        foreach ($this->fetcher->fetch($this->limit, $lastTimestamp) as $entry) {
            $this->logger->info('[Persist] ' . $entry->getTimestamp()->format('Y-m-d H:i:S'));

            if ($this->persister->wasEntryAlreadyFetched($entry)) {
                $this->logger->info('.. Already present, skipping...');
                continue;
            }

            $this->persister->add($entry);
        }

        $this->logger->info('Flushing collected entries');
        $this->persister->flush();
    }
}
