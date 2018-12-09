<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Repository\CollectionRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CollectionDoctrineRepository extends ServiceEntityRepository implements CollectionRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BackupCollection::class);
    }

    /**
     * @param BackupCollection $collection
     *
     * @return BackupCollection|null
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function merge(BackupCollection $collection): ?BackupCollection
    {
        return $this->getEntityManager()->merge($collection);
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function persist(BackupCollection $collection): void
    {
        $this->getEntityManager()->persist($collection);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flushAll(): void
    {
        $this->getEntityManager()->flush();
    }
}
