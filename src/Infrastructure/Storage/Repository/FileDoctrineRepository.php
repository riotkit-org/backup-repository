<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Repository;

use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\ValueObject\Checksum;
use App\Domain\Storage\ValueObject\Filename;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;

class FileDoctrineRepository extends ServiceEntityRepository implements FileRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StoredFile::class);
    }

    /**
     * Find a file in the registry by it's name
     *
     * @param Filename $filename
     *
     * @return StoredFile|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByName(Filename $filename): ?StoredFile
    {
        $qb = $this->createQueryBuilder('sf');
        $qb->where('sf.fileName = :filename')
            ->setParameter('filename', $filename->getValue());

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $exception) {
            return null;
        }
    }

    /**
     * Find a file by it's content (matching the checksum)
     *
     * @param Checksum $checksum
     *
     * @return StoredFile|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByHash(Checksum $checksum): ?StoredFile
    {
        $qb = $this->createQueryBuilder('sf');
        $qb->where('sf.contentHash = :contentHash')
            ->setParameter('contentHash', $checksum->getValue());

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $exception) {
            return null;
        }
    }

    /**
     * @param StoredFile $file
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function persist(StoredFile $file): void
    {
        $this->getEntityManager()->persist($file);
    }

    /**
     * @param null|StoredFile|StoredFile[] $files
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush($files = null): void
    {
        $this->getEntityManager()->flush($files);
    }

    public function delete(StoredFile $file): void
    {
        $this->getEntityManager()->remove($file);
    }
}
