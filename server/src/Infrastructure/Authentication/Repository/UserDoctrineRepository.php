<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Repository;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\UserAlreadyExistsException;
use App\Domain\Authentication\Helper\IdHidingHelper;
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
        parent::__construct($registry, User::class, $readOnly);
    }

    public function persist(User $user): void
    {
        if (!$user->canBePersisted()) {
            throw new \LogicException(
                'Attempting to persist a user, that cannot be persisted. ' .
                'Maybe it is a session user object?'
            );
        }

        $this->_em->persist($user);
    }

    public function flush(): void
    {
        try {
            $this->_em->flush();

        } catch (UniqueConstraintViolationException $exception) {
            throw new UserAlreadyExistsException($exception);
        }
    }

    public function remove(User $token): void
    {
        $this->_em->remove($token);
    }

    public function deactivate(User $token): void
    {
        $token->deactivate();
        $this->persist($token);
    }

    public function getExpiredUserAccounts(): array
    {
        $qb = $this->createQueryBuilder('token');
        $qb->where('token.expirationDate.value <= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d H:i:s'));

        return $qb->getQuery()->getResult();
    }

    public function findUsersBy(string $pattern, int $page = 1, int $count = 50, bool $searchById = true): array
    {
        $qb = $this->createQueryFindTokensBy($pattern, $searchById);

        return $this->paginate($qb, $page, $count)->getQuery()->getResult();
    }

    public function findMaxPagesOfUsersBy(string $pattern, int $limit = 50, bool $searchById = true): int
    {
        $qb = $this->createQueryFindTokensBy($pattern);
        $qb->select('COUNT(token)');

        return (int) ceil($qb->getQuery()->getSingleScalarResult() / $limit);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email.value' => $email]);
    }

    public function findOneById(string $id): ?User
    {
        return $this->findOneBy(['id' => $id]);
    }

    private function createQueryFindTokensBy(string $pattern, bool $searchById = true)
    {
        $qb = $this->createQueryBuilder('token');

        // searching by parts of id could be forbidden for security reasons
        if ($searchById) {
            $qb->andWhere('token.id LIKE :pattern');
        }

        $qb->orWhere('CAST(token.roles.value as STRING) LIKE :pattern');

        $qb->setParameters(['pattern' => '%' . $pattern . '%']);

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
