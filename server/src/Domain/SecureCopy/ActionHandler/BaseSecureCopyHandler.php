<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\ActionHandler;

use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\Replication\Provider\ConfigurationProvider;
use App\Domain\SecureCopy\Security\MirroringContext;
use Psr\Log\LoggerInterface;

abstract class BaseSecureCopyHandler
{
    private ConfigurationProvider $configurationProvider;
    protected LoggerInterface     $logger;

    public function setConfigurationProvider(ConfigurationProvider $provider): void
    {
        $this->configurationProvider = $provider;
    }

    protected function assertHasRights(MirroringContext $securityContext): void
    {
        if (!$securityContext->canStreamCopies()) {
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
