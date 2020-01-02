<?php declare(strict_types=1);

namespace App\Domain\Authentication\Repository;

use App\Domain\Authentication\Entity\Token;

interface TokenRepository extends \App\Domain\Common\Repository\TokenRepository
{
    public function persist(Token $token): void;
    public function remove(Token $token): void;
    public function flush(Token $token = null): void;

    /**
     * @return Token[]
     */
    public function getExpiredTokens(): array;
}
