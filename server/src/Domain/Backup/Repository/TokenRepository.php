<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\Token;

interface TokenRepository
{
    /**
     * @param string $id
     *
     * @return Token|null
     */
    public function findTokenById(string $id): ?Token;
}
