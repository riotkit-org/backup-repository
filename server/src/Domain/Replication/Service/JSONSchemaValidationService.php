<?php declare(strict_types=1);

namespace App\Domain\Replication\Service;

interface JSONSchemaValidationService
{
    public function validateAndGetErrorsAsText(array $data, array $schema): string;
}
