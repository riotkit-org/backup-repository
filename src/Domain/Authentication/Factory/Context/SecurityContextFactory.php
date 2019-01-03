<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory\Context;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Roles;

class SecurityContextFactory
{
    public function createFromToken(Token $token): AuthenticationManagementContext
    {
        return new AuthenticationManagementContext(
            $token->hasRole(Roles::ROLE_LOOKUP_TOKENS),
            $token->hasRole(Roles::ROLE_GENERATE_TOKENS),
            $token->hasRole(Roles::ROLE_USE_TECHNICAL_ENDPOINTS)
        );
    }

    public function createShellContext(): AuthenticationManagementContext
    {
        return new AuthenticationManagementContext(true, true, true);
    }
}
