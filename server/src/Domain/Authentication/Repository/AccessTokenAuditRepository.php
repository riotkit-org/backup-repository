<?php declare(strict_types=1);

namespace App\Domain\Authentication\Repository;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;

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
}
