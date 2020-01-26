<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Repository\Client;

use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Exception\DatabaseException;
use App\Domain\Replication\Repository\Client\ReplicationHistoryRepository;
use App\Infrastructure\Common\Repository\BaseRepository;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineReplicationHistoryRepository extends BaseRepository implements ReplicationHistoryRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, ReplicationLogEntry::class, $readOnly);
    }

    public function findLastEntryTimestamp(): ?DateTimeImmutable
    {
        $qb = $this->createQueryBuilder('entry');
        $qb->select('entry');
        $qb->orderBy('entry.date', 'DESC');
        $qb->setMaxResults(1);

        try {
            /**
             * @var ReplicationLogEntry $entry
             */
            $entry = $qb->getQuery()->getSingleResult();

        } catch (NoResultException $e) {
            return null;
        }

        return $entry->getTimestamp();
    }

    public function wasEntryAlreadyFetched(ReplicationLogEntry $entry): bool
    {
        $qb = $this->createQueryBuilder('entry');
        $qb->select('COUNT(entry)');
        $qb->where('entry.contentHash = :hash');
        $qb->setParameter('hash', $entry->createHash());

        try {
            return $qb->getQuery()->getSingleScalarResult() > 0;

        } catch (NoResultException $e) {
            return false;

        } catch (NonUniqueResultException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), null, $e);
        }
    }

    public function persist(ReplicationLogEntry $entry): void
    {
        try {
            $this->getEntityManager()->persist($entry);

        } catch (ORMException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), null, $e);
        }
    }

    public function flush(): void
    {
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), null, $e);
        } catch (ORMException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), null, $e);
        }
    }

    public function findByContentHash(string $contentHash): ?ReplicationLogEntry
    {
        /**
         * @var ReplicationLogEntry|null $log
         */
        $log = $this->find($contentHash);

        return $log;
    }

    public function findNotFinishedTasksSince(\DateTime $since): array
    {
        $qb = $this->createQueryBuilder('entry');
        $qb->where('entry.queueUpdateDate < :since AND entry.status IN (:status)');
        $qb->setParameters([
            'since'  => $since,
            'status' => [ReplicationLogEntry::STATUS_ERROR, ReplicationLogEntry::STATUS_NOT_TAKEN]
        ]);

        return $qb->getQuery()->getResult();
    }
}
