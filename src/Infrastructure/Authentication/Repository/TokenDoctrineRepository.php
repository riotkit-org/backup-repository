<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Repository;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Repository\TokenRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TokenDoctrineRepository extends ServiceEntityRepository implements TokenRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    public function persist(Token $token): void
    {
        $this->_em->persist($token);
    }

    public function flush(Token $token = null): void
    {
        $this->_em->flush($token);
    }

    public function findTokenById(string $id): ?Token
    {
        return $this->find($id);
    }

    public function remove(Token $token): void
    {
        $this->_em->remove($token);
    }

    public function deactivate(Token $token): void
    {
        $token->deactivate();
        $this->persist($token);
    }

    public function getExpiredTokens(): array
    {
        $qb = $this->createQueryBuilder('token');
        $qb->where('token.expirationDate <= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d H:i:s'));

        return $qb->getQuery()->getResult();
    }
}
