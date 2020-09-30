<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory\Context;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Roles;

class SecurityContextFactory
{
    public function createFromUserAccount(Token $access): AuthenticationManagementContext
    {
        return new AuthenticationManagementContext(
            $access->hasRole(Roles::ROLE_LOOKUP_TOKENS),
            $access->hasRole(Roles::ROLE_GENERATE_TOKENS),
            $access->hasRole(Roles::ROLE_USE_TECHNICAL_ENDPOINTS),
            $access->hasRole(Roles::ROLE_ADMINISTRATOR),
            $access->hasRole(Roles::ROLE_REVOKE_TOKENS),
            $access->hasRole(Roles::ROLE_CREATE_PREDICTABLE_TOKEN_IDS),
            $access->hasRole(Roles::ROLE_SEARCH_FOR_TOKENS),
            $access->hasRole(Roles::ROLE_CANNOT_SEE_FULL_TOKEN_ID)
        );
    }

    public function createShellContext(): AuthenticationManagementContext
    {
        return new AuthenticationManagementContext(
            true, true,
            true, true,
            true, true,
            true, false
        );
    }
}
