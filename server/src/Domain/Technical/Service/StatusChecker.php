<?php declare(strict_types=1);

namespace App\Domain\Technical\Service;

interface StatusChecker
{
    public const STATUS_CONN_REFUSED      = 'Connection refused';
    public const STATUS_DB_NOT_READY      = 'Database not ready';
    public const STATUS_STORAGE_NOT_READY = 'Storage not ready';
    public const STATUS_NOT_HEALTHY       = 'Not healthy';
    public const STATUS_RUNNING           = 'Running';
    public const STATUS_NOT_AUTHORIZED    = 'Not authorized. Is the token valid?';

    public function getHealthStatus(string $url, string $code): string;
}