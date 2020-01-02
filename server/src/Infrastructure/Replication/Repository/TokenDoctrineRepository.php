<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Repository;

use App\Domain\Replication\Entity\Authentication\Token;
use App\Domain\Replication\Repository\TokenRepository;
use App\Infrastructure\Common\Repository\TokenDoctrineRepository as CommonTokenRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TokenDoctrineRepository extends CommonTokenRepository implements TokenRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, $this->getTokenClass(), $readOnly);
    }

    protected function getTokenClass(): string
    {
        return Token::class;
    }
}
