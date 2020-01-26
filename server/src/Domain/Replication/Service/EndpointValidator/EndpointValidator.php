<?php declare(strict_types=1);

namespace App\Domain\Replication\Service\EndpointValidator;

use App\Domain\Replication\Exception\SchemaValidationErrors;

interface EndpointValidator
{
    /**
     * @throws SchemaValidationErrors
     */
    public function checkCompatibility(): void;

    public function __toString(): string;
}
