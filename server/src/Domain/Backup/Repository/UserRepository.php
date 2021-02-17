<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\User;

interface UserRepository
{
    /**
     * @param string $id
     *
     * @return User|null
     */
    public function findUserById(string $id): ?User;
}
