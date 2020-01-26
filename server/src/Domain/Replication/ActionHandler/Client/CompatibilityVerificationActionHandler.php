<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler\Client;

use App\Domain\Replication\ActionHandler\BaseReplicationHandler;
use App\Domain\Replication\Exception\IncompatibleEndpointError;
use App\Domain\Replication\Exception\ReplicationException;
use App\Domain\Replication\Exception\SchemaValidationErrors;
use App\Domain\Replication\Manager\Client\CompatibilityManager;
use Psr\Log\LoggerInterface;

/**
 * Checks if the PRIMARY is compatible with THIS REPLICA.
 * It means in practice an example data exchange and validation.
 */
class CompatibilityVerificationActionHandler extends BaseReplicationHandler
{
    private CompatibilityManager $manager;

    public function __construct(CompatibilityManager $manager, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->logger  = $logger;
    }

    /**
     * @throws IncompatibleEndpointError
     * @throws ReplicationException
     */
    public function handle(): void
    {
        $this->assertReplicationConfiguredAsReplicaServer();

        $endpoints = $this->manager->getValidators();

        foreach ($endpoints as $endpoint) {
            $this->log(' -> Checking endpoint: ' . $endpoint);

            try {
                $endpoint->checkCompatibility();
            } catch (SchemaValidationErrors $exception) {
                throw new IncompatibleEndpointError(
                    'The PRIMARY server has incompatible endpoint: ' . $endpoint. ', details: ' . $exception->getMessage());
            }
        }
    }
}
