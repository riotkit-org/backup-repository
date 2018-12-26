<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Entity\StoredFile;
use App\Domain\Backup\Repository\StorageRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class StorageDoctrineRepository extends ServiceEntityRepository implements StorageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StoredFile::class);
    }

    public function findById($id): ?StoredFile
    {
        return $this->find($id);
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
