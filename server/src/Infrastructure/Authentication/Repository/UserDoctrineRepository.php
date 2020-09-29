<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Repository;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\UserAlreadyExistsException;
use App\Domain\Authentication\Helper\TokenSecrets;
use App\Domain\Authentication\Repository\UserRepository;
use App\Infrastructure\Common\Repository\TokenDoctrineRepository as CommonTokenRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @codeCoverageIgnore
 */
class UserDoctrineRepository extends CommonTokenRepository implements UserRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, Token::class, $readOnly);
    }

    public function persist(Token $token): void
    {
        if (!$token->canBePersisted()) {
            throw new \LogicException('Attempting to persist a token, that cannot be persisted');
        }

        $this->_em->persist($token);
    }

    public function flush(Token $token = null): void
    {
        try {
            $this->_em->flush($token);

        } catch (UniqueConstraintViolationException $exception) {
            throw new UserAlreadyExistsException($exception);
        }
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
        $qb->where('token.expirationDate.value <= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d H:i:s'));

        return $qb->getQuery()->getResult();
    }

    public function findTokensBy(string $pattern, int $page = 1, int $count = 50, bool $searchById = true): array
    {
        $qb = $this->createQueryFindTokensBy($pattern, $searchById);

        return $this->paginate($qb, $page, $count)->getQuery()->getResult();
    }

    public function findMaxPagesTokensBy(string $pattern, int $limit = 50, bool $searchById = true): int
    {
        $qb = $this->createQueryFindTokensBy($pattern);
        $qb->select('COUNT(token)');

        return (int) ceil($qb->getQuery()->getSingleScalarResult() / $limit);
    }

    private function createQueryFindTokensBy(string $pattern, bool $searchById = true)
    {
        $qb = $this->createQueryBuilder('token');

        // searching by parts of id could be forbidden for security reasons
        if ($searchById) {
            $qb->andWhere('token.id LIKE :pattern');
        }

        // search only in allowed parts of token "*****f40-**87-**7c-**bb-********e8c0" when user does not have permissions to see full tokens
        $qb->andWhere(TokenSecrets::generateDQLConcatString('token.id') . ' LIKE :pattern OR token.id = :token_id');
        $qb->orWhere('CAST(token.roles.value as STRING) LIKE :pattern');

        $qb->setParameters(['pattern' => '%' . $pattern . '%', 'token_id' => $pattern]);

        return $qb;
    }

    private function paginate($dql, $page = 1, $limit = 3): Paginator
    {
        $paginator = new Paginator($dql);
        $paginator->setUseOutputWalkers(false);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }
}
