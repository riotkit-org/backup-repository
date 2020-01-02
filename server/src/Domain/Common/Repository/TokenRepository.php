<?php declare(strict_types=1);

namespace App\Domain\Common\Repository;

interface TokenRepository
{
    public function findTokenById(string $id, string $className = null);
}
