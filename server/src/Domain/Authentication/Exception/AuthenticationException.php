<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\AuthenticationException as ExceptionFromCommon;
use App\Domain\Errors;

class AuthenticationException extends ExceptionFromCommon
{
    /**
     * @return static
     */
    public static function fromUsersCreationProhibition()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_CANNOT_CREATE_USERS,
            Errors::ERR_PERMISSION_CANNOT_CREATE_USERS
        );
    }

    /**
     * @return static
     */
    public static function fromUsersEditProhibition()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_CANNOT_EDIT_USERS,
            Errors::ERR_PERMISSION_CANNOT_EDIT_USERS
        );
    }

    /**
     * @return static
     */
    public static function fromCannotChangePassword()
    {
        return new static(
            Errors::ERR_MSG_CANNOT_CHANGE_PASSWORD,
            Errors::ERR_CANNOT_CHANGE_PASSWORD
        );
    }

    /**
     * @return static
     */
    public static function fromPredictableIdSelectionProhibition()
    {
        return new static(
            Errors::ERR_MSG_REQUEST_PREDICTABLE_ID_FORBIDDEN,
            Errors::ERR_REQUEST_PREDICTABLE_ID_FORBIDDEN,
        );
    }

    /**
     * @return static
     */
    public static function fromCredentialsNotFoundIssue()
    {
        return new static(
            Errors::ERR_MSG_REQUEST_NO_VALID_USER_FOUND,
            Errors::ERR_REQUEST_NO_VALID_USER_FOUND,
        );
    }

    /**
     * @return static
     */
    public static function fromCannotRevokeUserAccessToken()
    {
        return new static(
            Errors::ERR_MSG_CANNOT_REVOKE_ACCESS_TOKEN,
            Errors::ERR_CANNOT_REVOKE_ACCESS_TOKEN
        );
    }

    /**
     * @return static
     */
    public static function fromNoPermissionToLookupUser()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_ACCESS_TO_LOOKUP_USER,
            Errors::ERR_PERMISSION_NO_ACCESS_TO_LOOKUP_USER,
        );
    }

    /**
     * @return static
     */
    public static function fromNoPermissionToSearchForUsers()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_ACCESS_TO_SEARCH_USERS,
            Errors::ERR_PERMISSION_NO_ACCESS_TO_SEARCH_USERS,
        );
    }
}
