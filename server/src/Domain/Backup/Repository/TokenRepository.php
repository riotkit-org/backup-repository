<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\User;

interface TokenRepository
{
    /**
     * @param string $id
     *
     * @return User|null
     */
    public function findTokenById(string $id): ?User;
}
