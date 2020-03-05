<?php declare(strict_types=1);

namespace App\Domain\Common\Service;

interface CryptoService
{
    public function decode(string $input, string $alternativeSecret = null): string;

    public function encode(string $input, string $alternativeSecret = null): string;

    public function hash(string $input): string;
}
