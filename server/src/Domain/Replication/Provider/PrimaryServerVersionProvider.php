<?php declare(strict_types=1);

namespace App\Domain\Replication\Provider;

interface PrimaryServerVersionProvider
{
    public function getVersion(): string;
}
