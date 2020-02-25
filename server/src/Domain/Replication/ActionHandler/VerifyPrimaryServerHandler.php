<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Replication\Exception\IncompatiblePrimaryServerVersion;
use App\Domain\Replication\Exception\PrimaryLinkNotConfiguredError;
use App\Domain\Replication\Provider\ConfigurationProvider;
use App\Domain\Replication\Service\CompatibilityChecker;

class VerifyPrimaryServerHandler
{
    private CompatibilityChecker $checker;
    private ConfigurationProvider $configuration;

    public function __construct(CompatibilityChecker $checker, ConfigurationProvider $configuration)
    {
        $this->checker       = $checker;
        $this->configuration = $configuration;
    }

    /**
     * @throws IncompatiblePrimaryServerVersion
     * @throws PrimaryLinkNotConfiguredError
     */
    public function handle(): void
    {
        $this->configuration->assertIsConfiguredAsReplica();

        $this->checker->assertPrimaryIsCompatible();
    }
}
