<?php declare(strict_types=1);

namespace App\Domain;

final class SSLAlgorithms
{
    public const ALGORITHMS = [
        'aes-128-cbc', 'aes-128-cfb', 'aes-128-cfb1', 'aes-128-ecb', 'aes-256-cbc',
        'aes-256-ctr', 'des3', 'blowfish', ''
    ];
}
