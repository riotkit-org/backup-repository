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
    private bool $cannotSeeFullTokenIds;
    private ?User $user;

    public function __construct(
        bool $canLookup,
        bool $canGenerate,
        bool $canUseTechnicalEndpoints,
        bool $isAdministrator,
        bool $canRevokeTokens,
        bool $canCreateTokensWithPredictableIds,
        bool $canSearchForTokens,
        bool $cannotSeeFullTokenIds,
        ?User $user
    ) {
        $this->canLookup                         = $canLookup;
        $this->canGenerateTokens                 = $canGenerate;
        $this->canUseTechnicalEndpoints          = $canUseTechnicalEndpoints;
        $this->isAdministrator                   = $isAdministrator;
        $this->canRevokeTokens                   = $canRevokeTokens;
        $this->canCreateTokensWithPredictableIds = $canCreateTokensWithPredictableIds;
        $this->canSearchForTokens                = $canSearchForTokens;
        $this->cannotSeeFullTokenIds             = $cannotSeeFullTokenIds;
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

    // @todo
    public function cannotSeeFullUserIds(): bool
    {
        return $this->cannotSeeFullTokenIds;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
