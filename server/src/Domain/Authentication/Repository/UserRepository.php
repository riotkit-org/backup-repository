<?php declare(strict_types=1);

namespace App\Domain\Authentication\Repository;

use App\Domain\Authentication\Entity\Token;

interface UserRepository extends \App\Domain\Common\Repository\TokenRepository
{
    public function persist(Token $token): void;
    public function remove(Token $token): void;
    public function flush(Token $token = null): void;

    /**
     * @return Token[]
     */
    public function getExpiredTokens(): array;

    /**
     * @param string $pattern
     * @param int $page
     * @param int $count
     * @param bool $searchById
     *
     * @return Token[]
     */
    public function findUsersBy(string $pattern, int $page = 1, int $count = 50, bool $searchById = true): array;

    public function findMaxPagesOfUsersBy(string $pattern, int $limit = 50): int;
}
