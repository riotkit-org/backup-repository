<?php declare(strict_types=1);

namespace App\Domain\Authentication\Security\Context;

class AuthenticationManagementContext
{
    /**
     * @var bool
     */
    private $canLookup;

    public function __construct(bool $canLookup)
    {
        $this->canLookup = $canLookup;
    }

    public function canLookupAnyToken(): bool
    {
        return $this->canLookup;
    }
}
