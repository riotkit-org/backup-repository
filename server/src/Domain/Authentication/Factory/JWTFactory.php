<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory;

use App\Domain\Authentication\Entity\User;

interface JWTFactory
{
    /**
     * Create JWT token for user
     *
     * @param User $user
     * @param array|null $permissions
     * @param int $ttl
     *
     * @return string
     */
    public function createForUser(User $user, array $permissions = null, int $ttl = 86400 * 365 * 2): string;

    /**
     * Create a token payload from encoded JWT (reverse to createForUser())
     *
     * @param string $token
     *
     * @return array
     */
    public function createArrayFromToken(string $token): array;
}

