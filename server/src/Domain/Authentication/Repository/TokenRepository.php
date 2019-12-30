<?php declare(strict_types=1);

namespace App\Domain\Authentication\Repository;

use App\Domain\Authentication\Entity\Token;

interface TokenRepository
{
    public function persist(Token $token): void;
    public function remove(Token $token): void;
    public function flush(Token $token = null): void;

    public function findTokenById(string $id, string $className = Token::class);

    /**
     * @return Token[]
     */
    public function getExpiredTokens(): array;
}
