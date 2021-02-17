<?php declare(strict_types=1);

namespace App\Domain\Authentication\Configuration;

class JWTConfiguration
{
    private string $lifetime;

    public function __construct(string $lifetime)
    {
        $this->lifetime = $lifetime;
    }

    public function getDefaultLifetime(): string
    {
        return $this->lifetime;
    }
}
