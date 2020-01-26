<?php declare(strict_types=1);

namespace App\Domain\Common\Repository;

use App\Domain\Authentication\Entity\Token;

interface TokenRepository
{
    public function findTokenById(string $id, string $className = null);

    public function findApplicationInternalToken(): Token;
}
