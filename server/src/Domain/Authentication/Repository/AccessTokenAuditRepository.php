<?php declare(strict_types=1);

namespace App\Domain\Authentication\Repository;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\RepeatableJWTException;

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

    /**
     * @throws RepeatableJWTException
     */
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
     * Finds Access Token entry by secret that is used by the user
     *
     * @param string $secret
     *
     * @return AccessTokenAuditEntry|null
     */
    public function findByBearerSecret(string $secret): ?AccessTokenAuditEntry;

    /**
     * Finds how many results are for findForUser()
     *
     * @param User $user
     * @param integer $limitPerPage
     *
     * @return int
     */
    public function findMaxPagesForUser(User $user, int $limitPerPage): int;
}
