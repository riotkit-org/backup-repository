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
        'no_permission_to_assign_custom_id'  => 4032,
        'token_invalid'                      => 40000001,
        'no_permissions_for_predictable_ids' => 40000002
    ];
}
