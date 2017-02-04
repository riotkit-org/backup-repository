<?php declare(strict_types=1);

namespace Factory;

use Model\Entity\Token;

/**
 * @package Factory\TokenFactory
 */
class TokenFactory
{
    /**
     * @param array     $roles
     * @param \DateTime $expires
     *
     * @return Token
     */
    public function createNewToken(array $roles, \DateTime $expires): Token
    {
        return (new Token())
            ->setId(uniqid('', true))
            ->setRoles($roles)
            ->setExpirationDate($expires);
    }
}