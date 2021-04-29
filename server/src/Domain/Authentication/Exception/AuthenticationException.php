<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\AuthenticationException as ExceptionFromCommon;
use App\Domain\Errors;

class AuthenticationException extends ExceptionFromCommon
{
    public static function fromUsersCreationProhibition(): static
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_CANNOT_CREATE_USERS,
            Errors::ERR_PERMISSION_CANNOT_CREATE_USERS
        );
    }

    public static function fromUsersEditProhibition(): static
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_CANNOT_EDIT_USERS,
            Errors::ERR_PERMISSION_CANNOT_EDIT_USERS
        );
    }

    public static function fromCannotChangePassword(): static
    {
        return new static(
            Errors::ERR_MSG_CANNOT_CHANGE_PASSWORD,
            Errors::ERR_CANNOT_CHANGE_PASSWORD
        );
    }

    public static function fromPredictableIdSelectionProhibition(): static
    {
        return new static(
            Errors::ERR_MSG_REQUEST_PREDICTABLE_ID_FORBIDDEN,
            Errors::ERR_REQUEST_PREDICTABLE_ID_FORBIDDEN,
        );
    }

    public static function fromCredentialsNotFoundIssue(): static
    {
        return new static(
            Errors::ERR_MSG_REQUEST_NO_VALID_USER_FOUND,
            Errors::ERR_REQUEST_NO_VALID_USER_FOUND,
        );
    }

    public static function fromCannotRevokeUserAccessToken(): static
    {
        return new static(
            Errors::ERR_MSG_CANNOT_REVOKE_ACCESS_TOKEN,
            Errors::ERR_CANNOT_REVOKE_ACCESS_TOKEN
        );
    }

    public static function fromNoPermissionToLookupUser(): static
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_ACCESS_TO_LOOKUP_USER,
            Errors::ERR_PERMISSION_NO_ACCESS_TO_LOOKUP_USER,
        );
    }

    public static function fromNoPermissionToSearchForUsers(): static
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_ACCESS_TO_SEARCH_USERS,
            Errors::ERR_PERMISSION_NO_ACCESS_TO_SEARCH_USERS,
        );
    }

    public static function fromAccountDeactivated(): static
    {
        return new static(
            Errors::ERR_MSG_USER_ACCOUNT_DEACTIVATED,
            Errors::ERR_USER_ACCOUNT_DEACTIVATED
        );
    }

    public static function fromAccountAccessDeniedBySecurityReason(): static
    {
        return new static(
            Errors::ERR_MSG_USER_ACCOUNT_DENIED_SECURITY_REASON,
            Errors::ERR_USER_ACCOUNT_DENIED_SECURITY_REASON
        );
    }

    public static function fromAccessTokenManuallyDeactivatedReason(): static
    {
        return new static(
            Errors::ERR_MSG_JWT_MANUALLY_REVOKED,
            Errors::ERR_JWT_MANUALLY_REVOKED
        );
    }
}
