<?php declare(strict_types=1);

namespace App\Domain\Authentication\Security\Context;

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

    public function __construct(bool $canLookup, bool $canGenerate, bool $canUseTechnicalEndpoints)
    {
        $this->canLookup                = $canLookup;
        $this->canGenerateTokens        = $canGenerate;
        $this->canUseTechnicalEndpoints = $canUseTechnicalEndpoints;
    }

    public function canLookupAnyToken(): bool
    {
        return $this->canLookup;
    }

    public function canGenerateNewToken(): bool
    {
        return $this->canGenerateTokens;
    }

    public function canUseTechnicalEndpoints(): bool
    {
        return $this->canUseTechnicalEndpoints;
    }
}
