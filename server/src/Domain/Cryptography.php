<?php declare(strict_types=1);

namespace App\Domain;

final class Cryptography
{
    public const DIGEST_ALGORITHMS = [
        'sha256', 'sha512'
    ];

    public const CRYPTO_ALGORITHMS = [
        'aes-256-cbc', ''
    ];

    public const KEY_SIZES = [
        'aes-256-cbc' => 16,
        ''            => 0
    ];

    public const KEY_BITS = [
        'aes-256-cbc' => 256,
        ''            => 0
    ];

    public const DEFAULT_DIGEST_ROUNDS = 6000;
}
