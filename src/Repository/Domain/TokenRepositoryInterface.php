<?php declare(strict_types=1);

namespace Repository\Domain;
use Model\Entity\Token;

/**
 * @package Repository\Domain
 */
interface TokenRepositoryInterface
{
    /**
     * @param string $tokenId
     * @return null|Token
     */
    public function getTokenById($tokenId);

    /**
     * @return Token[]
     */
    public function getExpiredTokens(): array;
}