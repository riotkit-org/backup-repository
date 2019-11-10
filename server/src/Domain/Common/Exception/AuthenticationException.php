<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

/**
 * @codeCoverageIgnore
 */
class AuthenticationException extends \Exception
{
    public const CODES = [
        'not_authenticated'                  => 403,
        'no_read_access_or_invalid_password' => 401,
        'auth_cannot_delete_file'            => 4031,
        'token_invalid'                      => 40000001
    ];
}
