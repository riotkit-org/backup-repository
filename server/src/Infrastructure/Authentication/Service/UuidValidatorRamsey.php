<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Service;

use App\Domain\Authentication\Service\UuidValidator;
use Ramsey\Uuid\Uuid;

class UuidValidatorRamsey implements UuidValidator
{
    public function isValid(string $uuidStr): bool
    {
        return Uuid::isValid($uuidStr);
    }
}