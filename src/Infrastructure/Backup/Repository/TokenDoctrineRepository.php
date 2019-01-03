<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\Token;
use App\Domain\Backup\Repository\TokenRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TokenDoctrineRepository extends ServiceEntityRepository implements TokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    /**
     * @inheritdoc
     */
    public function findTokenById(string $id): ?Token
    {
        return $this->find($id);
    }
}
