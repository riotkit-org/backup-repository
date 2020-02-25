<?php declare(strict_types=1);

namespace App\Domain\Common\Service;

interface CryptoService
{
    public function decode(string $input): string;

    public function encode(string $input): string;

    public function hash(string $input): string;
}
