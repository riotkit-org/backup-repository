<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory\Context;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Roles;

class SecurityContextFactory
{
    public function createFromUserAccount(User $access): AuthenticationManagementContext
    {
        return new AuthenticationManagementContext(
            $access->hasRole(Roles::ROLE_LOOKUP_TOKENS),
            $access->hasRole(Roles::ROLE_GENERATE_TOKENS),
            $access->hasRole(Roles::ROLE_USE_TECHNICAL_ENDPOINTS),
            $access->hasRole(Roles::ROLE_ADMINISTRATOR),
            $access->hasRole(Roles::ROLE_DELETE_USERS),
            $access->hasRole(Roles::ROLE_CREATE_PREDICTABLE_TOKEN_IDS),
            $access->hasRole(Roles::ROLE_SEARCH_FOR_TOKENS),
            $access->hasRole(Roles::ROLE_CAN_SEE_SELF_USER_ACCESS_TOKENS),
            $access->hasRole(Roles::ROLE_CAN_LIST_ALL_USERS_ACCESS_TOKENS),
            $access->hasRole(Roles::ROLE_CAN_REVOKE_OWN_ACCESS_TOKEN),
            $access->hasRole(Roles::ROLE_CAN_REVOKE_TOKENS_OF_OTHER_USERS),
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
            true, true,
            null
        );
    }
}
