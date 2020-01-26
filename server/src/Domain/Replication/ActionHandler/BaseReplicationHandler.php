<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Exception\ReplicationException;
use App\Domain\Replication\Provider\ConfigurationProvider;
use App\Domain\Replication\Security\ReplicationContext;
use Psr\Log\LoggerInterface;

abstract class BaseReplicationHandler
{
    /**
     * @var callable|null
     */
    private $loggingCallback;
    private ConfigurationProvider $configurationProvider;
    protected LoggerInterface     $logger;

    public function setLoggingCallback(callable $callback): void
    {
        $this->loggingCallback = $callback;
    }

    public function setConfigurationProvider(ConfigurationProvider $provider): void
    {
        $this->configurationProvider = $provider;
    }

    protected function assertReplicationConfiguredAsReplicaServer(): void
    {
        if (!$this->configurationProvider->isNodeConfiguredAsReplica()) {
            throw new ReplicationException('Replication not configured as replica node');
        }
    }

    protected function assertHasRights(ReplicationContext $securityContext): void
    {
        if (!$securityContext->canRunReplication()) {
            throw new AuthenticationException(
                'Current token does not allow to replicate the data',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }

    protected function log(string $message, string $severity = 'info'): void
    {
        $this->logger->$severity($message);
    }
}
