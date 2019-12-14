<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

use App\Domain\Common\Service\UuidValidator;
use Ramsey\Uuid\Uuid;

class UuidValidatorRamsey implements UuidValidator
{
    public function isValid(string $uuidStr): bool
    {
        return Uuid::isValid($uuidStr);
    }
}
