<?php declare(strict_types=1);

namespace App\Domain\Replication\Service;

use App\Domain\Replication\Exception\ReplicaNodeUnhealthyError;
use App\Domain\Replication\Provider\ConfigurationProvider;
use App\Domain\Replication\Repository\Client\ReplicationHistoryRepository;

class ReplicationStatusCheck
{
    private ReplicationHistoryRepository $repository;
    private ConfigurationProvider $configurationProvider;

    public function __construct(ReplicationHistoryRepository $repository, ConfigurationProvider $configurationProvider)
    {
        $this->repository = $repository;
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * @throws ReplicaNodeUnhealthyError
     */
    public function assertIsHealthy(): void
    {
        // @todo: Verify last running date

        if (!$this->configurationProvider->isNodeConfiguredAsReplica()) {
            return;
        }

        $errorsCount = $this->repository->getErrorsCount();

        if ($errorsCount > 0) {
            throw new ReplicaNodeUnhealthyError('The node has ' . $errorsCount . ' errors');
        }
    }

    public function isConfiguredAsReplica(): bool
    {
        return $this->configurationProvider->isNodeConfiguredAsReplica();
    }
}
