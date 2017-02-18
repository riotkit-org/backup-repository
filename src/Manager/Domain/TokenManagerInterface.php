<?php declare(strict_types=1);

namespace Manager\Domain;

use Model\Entity\Token;

interface TokenManagerInterface
{
    /**
     * Validate if having a token X we could access resource Y
     *
     * @param string $tokenId
     * @param array  $requiredRoles
     *
     * @return bool
     */
    public function isTokenValid(string $tokenId, array $requiredRoles = []): bool;

    /**
     * Check if input token is an admin token (a global defined token in configuration files)
     *
     * @param string $tokenId
     * @return bool
     */
    public function isAdminToken(string $tokenId): bool;

    /**
     * Generate a new token with random id
     *
     * @param array     $roles
     * @param \DateTime $expires
     * @param array     $data
     *
     * @return Token
     */
    public function generateNewToken(array $roles, \DateTime $expires, array $data = []): Token;

    /**
     * @param Token $token
     */
    public function removeToken(Token $token);
}
