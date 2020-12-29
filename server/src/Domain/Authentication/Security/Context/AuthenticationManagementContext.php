<?php declare(strict_types=1);

namespace App\Domain\Authentication\Security\Context;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Service\RolesFilter;
use App\Domain\Roles;

class AuthenticationManagementContext
{
    private bool $canLookup;
    private bool $canGenerateTokens;
    private bool $canUseTechnicalEndpoints;
    private bool $isAdministrator;
    private bool $canRevokeTokens;
    private bool $canCreateTokensWithPredictableIds;
    private bool $canSearchForTokens;
    private bool $canListSelfAccessTokens;
    private bool $canListAllUsersAccessTokens;
    private ?User $user;

    public function __construct(
        bool $canLookup,
        bool $canGenerate,
        bool $canUseTechnicalEndpoints,
        bool $isAdministrator,
        bool $canRevokeTokens,
        bool $canCreateTokensWithPredictableIds,
        bool $canSearchForTokens,
        bool $canListSelfAccessTokens,
        bool $canListAllUsersAccessTokens,
        ?User $user
    ) {
        $this->canLookup                         = $canLookup;
        $this->canGenerateTokens                 = $canGenerate;
        $this->canUseTechnicalEndpoints          = $canUseTechnicalEndpoints;
        $this->isAdministrator                   = $isAdministrator;
        $this->canRevokeTokens                   = $canRevokeTokens;
        $this->canCreateTokensWithPredictableIds = $canCreateTokensWithPredictableIds;
        $this->canSearchForTokens                = $canSearchForTokens;
        $this->canListSelfAccessTokens           = $canListSelfAccessTokens;
        $this->canListAllUsersAccessTokens       = $canListAllUsersAccessTokens;
        $this->user                              = $user;
    }

    public function canLookupAnyUserAccount(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canLookup;
    }

    public function canSearchForUsers(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canLookupAnyUserAccount() && $this->canSearchForTokens;
    }

    public function canGenerateNewToken(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canGenerateTokens;
    }

    public function canUseTechnicalEndpoints(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canUseTechnicalEndpoints;
    }

    public function canRevokeAccess(User $user): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        // a non-administrator cannot revoke access for the administrator
        if (!$this->isAdministrator && $user->hasRole(Roles::ROLE_ADMINISTRATOR)) {
            return false;
        }

        return $this->canRevokeTokens;
    }

    public function canCreateUsersWithPredictableIdentifiers(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canCreateTokensWithPredictableIds;
    }

    public function canGenerateJWTWithSelectedPermissions(array $roles): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        // remove all roles that user does not have
        $filteredByPermissions = RolesFilter::filterBy($roles, [RolesFilter::FILTER_AUTH], $this->user);
        sort($roles);
        sort($filteredByPermissions);

        // check that RolesFilter didn't reject any role
        return $filteredByPermissions === $roles;
    }

    /**
     * - Normally user can only see it's own access tokens (without JWT)
     * - With additional permission user can see other people access tokens (without JWT)
     *
     * @param User $user
     *
     * @return bool
     */
    public function canListUserAccessTokens(User $user): bool
    {
        if ($this->user->getId() === $user->getId()) {
            return $this->canListSelfAccessTokens;
        }

        return $this->canListAllUsersAccessTokens;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
