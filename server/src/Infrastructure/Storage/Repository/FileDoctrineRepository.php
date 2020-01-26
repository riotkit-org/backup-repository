<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Repository;

use App\Infrastructure\Common\Repository\BaseRepository;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Parameters\Repository\FindByParameters;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\ValueObject\Checksum;
use App\Domain\Storage\ValueObject\Filename;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class FileDoctrineRepository extends BaseRepository implements FileRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, StoredFile::class, $readOnly);
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

    /**
     * @param FindByParameters $parameters
     *
     * @return StoredFile[]
     */
    public function findMultipleBy(FindByParameters $parameters): array
    {
        $qb = $this->createQueryBuilder('file');
        $this->appendMultipleByConditions($parameters, $qb);

        if ($parameters->limit >= 1 && $parameters->page >= 1) {
            $qb->setMaxResults($parameters->limit);
            $qb->setFirstResult(($parameters->page - 1) * $parameters->limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findExampleFile(): StoredFile
    {
        return StoredFile::newFromFilename(new Filename('example'));
    }

    /**
     * @param FindByParameters $parameters
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws NoResultException
     */
    public function getMultipleByPagesCount(FindByParameters $parameters): int
    {
        $qb = $this->createQueryBuilder('file');
        $qb->select('COUNT(file)');
        $this->appendMultipleByConditions($parameters, $qb);

        return (int) ceil($qb->getQuery()->getSingleScalarResult() / $parameters->limit);
    }

    private function appendMultipleByConditions(FindByParameters $parameters, QueryBuilder $qb): void
    {
        if ($parameters->searchQuery) {
            $qb->andWhere('file.fileName LIKE :searchQuery')
                ->setParameter('searchQuery', '%' . $parameters->searchQuery . '%');
        }

        if (\is_array($parameters->mimes) && $parameters->mimes) {
            $qb->andWhere('file.mimeType IN (:mimes)')
                ->setParameter('mimes', $parameters->mimes);
        }

        if (\is_array($parameters->tags) && $parameters->tags) {
            $qb->join('file.tags', 'tag');
            $qb->andWhere('tag.name IN (:tags)')
                ->setParameter('tags', $parameters->tags);
        }

        if ($parameters->public !== null) {
            $qb->andWhere('file.public = :public')
                ->setParameter('public', $parameters->public);
        }
    }
}
