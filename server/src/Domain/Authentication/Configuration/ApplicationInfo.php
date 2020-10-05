<?php declare(strict_types=1);

namespace App\Domain\Authentication\Configuration;

class ApplicationInfo
{
    /**
     * @var string $envType Choices: dev|test|prod
     */
    private string $envType;

    public function __construct(string $environmentType)
    {
        $this->envType = $environmentType;
    }

    public function isDebugEnvironment(): bool
    {
        return in_array($this->envType, ['dev', 'test'], true);
    }

    public function isDevelopmentEnvironment(): bool
    {
        return $this->envType === 'dev';
    }
}
