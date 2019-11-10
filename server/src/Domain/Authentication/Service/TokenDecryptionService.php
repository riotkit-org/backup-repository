<?php declare(strict_types=1);

namespace App\Domain\Authentication\Service;

interface TokenDecryptionService
{
    public function decode(string $input): string;
}
