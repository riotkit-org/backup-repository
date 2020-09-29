<?php declare(strict_types=1);

namespace App\Domain\Authentication\Security\Context;

use App\Domain\Authentication\Entity\Token;
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

    public function __construct(
        bool $canLookup,
        bool $canGenerate,
        bool $canUseTechnicalEndpoints,
        bool $isAdministrator,
        bool $canRevokeTokens,
        bool $canCreateTokensWithPredictableIds,
        bool $canSearchForTokens,
        bool $cannotSeeFullTokenIds
    ) {
        $this->canLookup                         = $canLookup;
        $this->canGenerateTokens                 = $canGenerate;
        $this->canUseTechnicalEndpoints          = $canUseTechnicalEndpoints;
        $this->isAdministrator                   = $isAdministrator;
        $this->canRevokeTokens                   = $canRevokeTokens;
        $this->canCreateTokensWithPredictableIds = $canCreateTokensWithPredictableIds;
        $this->canSearchForTokens                = $canSearchForTokens;
        $this->cannotSeeFullTokenIds             = $cannotSeeFullTokenIds;
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

    public function canRevokeAccess(Token $token): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        // a non-administrator cannot revoke access for the administrator
        if (!$this->isAdministrator && $token->hasRole(Roles::ROLE_ADMINISTRATOR)) {
            return false;
        }

        return $this->canRevokeTokens;
    }

    public function canCreateTokensWithPredictableIdentifiers(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canCreateTokensWithPredictableIds;
    }

    public function cannotSeeFullUserIds(): bool
    {
        return $this->cannotSeeFullTokenIds;
    }
}
