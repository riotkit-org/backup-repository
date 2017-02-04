<?php declare(strict_types=1);

namespace Manager\Domain;

use Model\Entity\Token;

interface TokenManagerInterface
{
    /**
     * Validate if having a token X we could access resource Y
     *
     * @param string $tokenId
     * @param string $roleName
     *
     * @return bool
     */
    public function isTokenValid(string $tokenId, string $roleName = ''): bool;

    /**
     * Generate a new token with random id
     *
     * @param array     $roles
     * @param \DateTime $expires
     *
     * @return Token
     */
    public function generateNewToken(array $roles, \DateTime $expires): Token;

    /**
     * @param Token $token
     */
    public function removeToken(Token $token);
}
