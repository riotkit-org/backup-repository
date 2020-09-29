<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

/**
 * @codeCoverageIgnore
 */
class AuthenticationException extends ApplicationException
{
    private const NOT_AUTHENTICATED = 403;


    public const CODES = [
        'not_authenticated'                  => 403,
        'no_read_access_or_invalid_password' => 401,
        'auth_cannot_delete_file'            => 4031,
        'no_permission_to_assign_custom_id'  => 4032,
        'token_invalid'                      => 40000001,
        'no_permissions_for_predictable_ids' => 40000002,
        'no_permissions_to_see_other_tokens' => 40000003
    ];

    /**
     * @return static
     */
    public static function createNotAuthenticatedError()
    {
        return new static('not_authenticated', 403);
    }

    /**
     * @return static
     */
    public static function createNoReadAccessOrInvalidPasswordError()
    {
        return new static('No access to read the file, maybe invalid password?', 401);
    }

    /**
     * @return static
     */
    public static function createCannotDeleteFileError()
    {
        return new static('auth_cannot_delete_file', 4031);
    }

    public static function createCannotAssignCustomIdError()
    {
        return new static('no_permission_to_assign_custom_id', 4032);
    }

    public static function createTokenInvalidError()
    {
        return new static('token_invalid', 40000001);
    }

    // @todo: Verify if this is the same as createCannotAssignCustomIdError()
    public static function createNoPermissionToAssignPredictableIdsError()
    {
        return new static('no_permissions_for_predictable_ids', 40000002);
    }

    public static function createNoPermissionToViewOtherTokens()
    {
        return new static('no_permissions_to_see_other_tokens', 40000003);
    }
}
