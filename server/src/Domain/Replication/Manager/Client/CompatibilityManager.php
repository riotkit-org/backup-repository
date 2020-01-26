<?php declare(strict_types=1);

namespace App\Domain\Replication\Manager\Client;

use App\Domain\Replication\Service\EndpointValidator\EndpointValidator;

class CompatibilityManager
{
    /**
     * @var EndpointValidator[] $validators
     */
    private array $validators;

    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * @return EndpointValidator[]
     */
    public function getValidators(): array
    {
        return $this->validators;
    }
}
