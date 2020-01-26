<?php declare(strict_types=1);

namespace App\Domain\Replication\Provider;

class ConfigurationProvider
{
    private string $token;
    private string $primaryUrl;
    private string $queueDsn;

    public function __construct(string $token, string $primaryUrl, string $queueDsn)
    {
        $this->token      = $token;
        $this->primaryUrl = $primaryUrl;
        $this->queueDsn   = $queueDsn;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPrimaryUrl(): string
    {
        return $this->primaryUrl;
    }

    public function getQueueDsn(): string
    {
        return $this->queueDsn;
    }

    public function isNodeConfiguredAsReplica(): bool
    {
        return $this->primaryUrl !== '' && $this->queueDsn !== '';
    }
}