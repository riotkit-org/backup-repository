<?php declare(strict_types=1);

namespace App\Domain\Authentication\Service;

interface CryptoService
{
    public function decode(string $input): string;
}
