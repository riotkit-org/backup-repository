<?php declare(strict_types=1);

namespace App\Domain\Authentication\Security\Context;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Roles;

class AuthenticationManagementContext
{
    /**
     * @var bool
     */
    private $canLookup;

    /**
     * @var bool
     */
    private $canGenerateTokens;

    /**
     * @var bool
     */
    private $canUseTechnicalEndpoints;

    /**
     * @var bool
     */
    private $isAdministrator;

    /**
     * @var bool
     */
    private $canRevokeTokens;

    /**
     * @var bool
     */
    private $canCreateTokensWithPredictableIds;

    public function __construct(
        bool $canLookup,
        bool $canGenerate,
        bool $canUseTechnicalEndpoints,
        bool $isAdministrator,
        bool $canRevokeTokens,
        bool $canCreateTokensWithPredictableIds
    ) {
        $this->canLookup                = $canLookup;
        $this->canGenerateTokens        = $canGenerate;
        $this->canUseTechnicalEndpoints = $canUseTechnicalEndpoints;
        $this->isAdministrator          = $isAdministrator;
        $this->canRevokeTokens          = $canRevokeTokens;
        $this->canCreateTokensWithPredictableIds = $canCreateTokensWithPredictableIds;
    }

    public function canLookupAnyToken(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canLookup;
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

    public function canRevokeToken(Token $token): bool
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
}
