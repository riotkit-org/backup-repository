<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Collection\VersionsCollection;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Service\Filesystem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class VersionDoctrineRepository extends ServiceEntityRepository implements VersionRepository
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var VersionsCollection[]
     */
    private $collectionVersionsCache = [];

    public function __construct(ManagerRegistry $registry, Filesystem $fs)
    {
        $this->fs = $fs;

        parent::__construct($registry, StoredVersion::class);
    }

    /**
     * @param BackupCollection $collection
     *
     * @return VersionsCollection
     */
    public function findCollectionVersions(BackupCollection $collection): VersionsCollection
    {
        $cacheId = \spl_object_hash($collection);

        if (isset($this->collectionVersionsCache[$cacheId])) {
            return $this->collectionVersionsCache[$cacheId];
        }

        $qb = $this->createQueryBuilder('version');
        $qb->where('version.collection = :collection');
        $qb->setParameter('collection', $collection);

        $versions = new VersionsCollection(
            $qb->getQuery()->getResult(),
            $collection,
            function ($filename) { return $this->fs->getFileSize($filename);  }
        );

        return $this->collectionVersionsCache[$cacheId] = $versions;
    }

    /**
     * @param StoredVersion $version
     *
     * @throws ORMException
     */
    public function delete(StoredVersion $version): void
    {
        $this->getEntityManager()->remove($version);
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
     * @param StoredVersion $version
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flush(StoredVersion $version): void
    {
        $this->getEntityManager()->flush($version);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushAll(): void
    {
        $this->getEntityManager()->flush();
    }
}
