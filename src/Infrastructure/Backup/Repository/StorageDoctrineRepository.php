<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Entity\StoredFile;
use App\Domain\Backup\Repository\StorageRepository;
use App\Domain\Common\Repository\BaseRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class StorageDoctrineRepository extends BaseRepository implements StorageRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, StoredFile::class, $readOnly);
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
