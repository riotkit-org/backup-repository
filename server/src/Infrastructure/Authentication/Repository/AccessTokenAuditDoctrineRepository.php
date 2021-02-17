<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Repository;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Repository\AccessTokenAuditRepository;
use App\Domain\Authentication\Service\Security\HashEncoder;
use App\Infrastructure\Common\Repository\BaseRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class AccessTokenAuditDoctrineRepository extends BaseRepository implements AccessTokenAuditRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, AccessTokenAuditEntry::class, $readOnly);
    }

    public function persist(AccessTokenAuditEntry $entry): void
    {
        $this->getEntityManager()->persist($entry);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function isActiveToken(string $jwt): bool
    {
        $hash = HashEncoder::encode($jwt);

        /**
         * @var AccessTokenAuditEntry|null $match
         */
        $match = $this->findOneBy(['tokenHash' => $hash]);

        if (!$match) {
            return false;
        }

        return $match->isStillValid();
    }

    public function findForUser(User $user, int $page, int $perPage): array
    {
        $qb = $this->createQueryBuilder('access_token');
        $qb->addSelect('(CASE WHEN access_token.expiration > :now AND access_token.active = true THEN true ELSE false END) AS HIDDEN is_valid');
        $qb->where('access_token.user = :user');
        $qb->setParameter('user', $user);
        $qb->setParameter('now', new \DateTime());
        $qb->addOrderBy('is_valid', 'DESC');
        $qb->addOrderBy('access_token.date', 'DESC');

        return $this->paginate($qb, $page, $perPage)->getQuery()->getResult();
    }

    public function findMaxPagesForUser(User $user, int $limitPerPage): int
    {
        $qb = $this->createQueryBuilder('access_token');
        $qb->select('COUNT(access_token)');
        $qb->where('access_token.user = :user');
        $qb->setParameter('user', $user);

        return (int) ceil($qb->getQuery()->getSingleScalarResult() / $limitPerPage);
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

    public function findByTokenHash(string $tokenHash): ?AccessTokenAuditEntry
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }
}
