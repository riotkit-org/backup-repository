<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

class AuthenticationException extends \Exception
{
    public const CODES = [
        'not_authenticated' => 403,
        'token_invalid'     => 40000001
    ];
}
