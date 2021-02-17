<?php declare(strict_types=1);

namespace App\Domain\Authentication\Service\Security;

class HashEncoder
{
    private const ROUNDS = 10000;

    public static function encode(string $input): string
    {
        $hash = $input;

        for ($i = 0; $i < static::ROUNDS; $i++) {
            $hash = hash('sha256', $hash . $i);
        }

        return $hash;
    }
}
