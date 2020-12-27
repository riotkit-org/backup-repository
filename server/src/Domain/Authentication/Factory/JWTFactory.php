<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory;

use App\Domain\Authentication\Entity\User;

interface JWTFactory
{
    public function createForUser(User $user, array $permissions = null, int $ttl = 86400 * 365 * 2): string;
}

