<?php declare(strict_types=1);

namespace App\Domain\Common\Service;

interface UuidValidator
{
    public function isValid(string $uuidStr): bool;
}
