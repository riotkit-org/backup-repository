<?php declare(strict_types=1);

namespace App\Domain\Common\Repository;

use App\Domain\Authentication\Entity\User;

interface TokenRepository
{
    public function findUserByUserId(string $id, string $className = null);

    public function findApplicationInternalToken(): User;
}
