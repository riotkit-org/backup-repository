<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory\Context;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\PermissionsReference;

class SecurityContextFactory
{
    public function createFromUserAccount(User $access): AuthenticationManagementContext
    {
        return new AuthenticationManagementContext(
            $access->hasRole(PermissionsReference::PERMISSION_LOOKUP_USERS),
            $access->hasRole(PermissionsReference::PERMISSION_CREATE_UNLIMITED_USER_ACCOUNTS),
            $access->hasRole(PermissionsReference::PERMISSION_USE_TECHNICAL_ENDPOINTS),
            $access->hasRole(PermissionsReference::PERMISSION_ADMINISTRATOR),
            $access->hasRole(PermissionsReference::PERMISSION_DELETE_USERS),
            $access->hasRole(PermissionsReference::PERMISSION_CREATE_PREDICTABLE_USER_IDS),
            $access->hasRole(PermissionsReference::PERMISSION_SEARCH_FOR_USERS),
            $access->hasRole(PermissionsReference::PERMISSION_CAN_SEE_SELF_USER_ACCESS_TOKENS),
            $access->hasRole(PermissionsReference::PERMISSION_CAN_LIST_ALL_USERS_ACCESS_TOKENS),
            $access->hasRole(PermissionsReference::PERMISSION_CAN_REVOKE_OWN_ACCESS_TOKEN),
            $access->hasRole(PermissionsReference::PERMISSION_CAN_REVOKE_TOKENS_OF_OTHER_USERS),
            $access->hasRole(PermissionsReference::PERMISSION_CAN_LIST_PERMISSIONS),
            $access->hasRole(PermissionsReference::PERMISSION_CAN_GENERATE_NEW_CUSTOM_TOKEN_FOR_SELF),
            $access
        );
    }

    public function createShellContext(): AuthenticationManagementContext
    {
        return new AuthenticationManagementContext(
            true, true,
            true, true,
            true, true,
            true, true, true,
            true, true, true,
            true, null
        );
    }
}
