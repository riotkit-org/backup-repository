<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Collection\VersionsCollection;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\DomainBus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class VersionDoctrineRepository extends ServiceEntityRepository implements VersionRepository
{
    /**
     * @var DomainBus
     */
    private $domainBus;

    public function __construct(ManagerRegistry $registry, DomainBus $domainBus)
    {
        $this->domainBus = $domainBus;

        parent::__construct($registry, StoredVersion::class);
    }

    /**
     * @param StoredVersion $collection
     *
     * @throws ORMException
     */
    public function persist(StoredVersion $collection): void
    {
        $this->getEntityManager()->persist($collection);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushAll(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param BackupCollection $collection
     *
     * @return VersionsCollection
     */
    public function findCollectionVersions(BackupCollection $collection): VersionsCollection
    {
        $qb = $this->createQueryBuilder('version');
        $qb->where('version.collection = :collection');
        $qb->setParameter('collection', $collection);

        return new VersionsCollection(
            $qb->getQuery()->getResult(),
            $collection,
            function (...$args) { return $this->domainBus->call(Bus::STORAGE_GET_FILE_SIZE, $args);  }
        );
    }
}
