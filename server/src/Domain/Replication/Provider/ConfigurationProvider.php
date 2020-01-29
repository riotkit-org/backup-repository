<?php declare(strict_types=1);

namespace App\Domain\Replication\Provider;

class ConfigurationProvider
{
    private string $token;
    private string $primaryUrl;

    public function __construct(string $token, string $primaryUrl)
    {
        $this->token      = $token;
        $this->primaryUrl = rtrim($primaryUrl, ' /');
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPrimaryUrl(): string
    {
        return $this->primaryUrl;
    }

    public function isNodeConfiguredAsReplica(): bool
    {
        return $this->primaryUrl !== '';
    }
}
