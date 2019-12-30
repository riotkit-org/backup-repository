<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Security\ReplicationContext;

abstract class BaseReplicationHandler
{
    protected function assertHasRights(ReplicationContext $securityContext): void
    {
        if (!$securityContext->canRunReplication()) {
            throw new AuthenticationException(
                'Current token does not allow to replicate the data',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
