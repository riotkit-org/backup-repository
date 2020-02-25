<?php declare(strict_types=1);

namespace App\Domain\Replication;

final class ReplicaDomain
{
    public const REQUEST_MODE_LOCAL   = 'local';
    public const REQUEST_MODE_FORWARD = 'redirect-to-primary';
}
