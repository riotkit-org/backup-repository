<?php declare(strict_types=1);

namespace App\Domain\Authentication\Repository;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use App\Domain\Authentication\Entity\User;

interface AccessTokenAuditRepository
{
    /**
     * Checks if there is a existing, valid JWT token permitted to use
     *
     * @param string $jwt
     *
     * @return bool
     */
    public function isActiveToken(string $jwt): bool;

    public function persist(AccessTokenAuditEntry $entry): void;

    public function flush(): void;

    /**
     * @param User $user
     * @param int $page
     * @param int $perPage
     *
     * @return AccessTokenAuditEntry[]
     */
    public function findForUser(User $user, int $page, int $perPage): array;

    /**
     * @param string $tokenHash
     *
     * @return AccessTokenAuditEntry|null
     */
    public function findByTokenHash(string $tokenHash): ?AccessTokenAuditEntry;

    /**
     * Finds how many results are for findForUser()
     *
     * @param User $user
     *
     * @return int
     */
    public function findMaxPagesForUser(User $user): int;
}
