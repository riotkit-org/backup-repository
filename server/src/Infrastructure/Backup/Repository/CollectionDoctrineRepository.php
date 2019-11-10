<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Parameters\Repository\ListingParameters;
use App\Domain\Backup\Repository\CollectionRepository;
use App\Domain\Common\Repository\BaseRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class CollectionDoctrineRepository extends BaseRepository implements CollectionRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, BackupCollection::class, $readOnly);
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

    /**
     * @param BackupCollection $collection
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete(BackupCollection $collection): void
    {
        $this->getEntityManager()->remove($collection);
    }

    /**
     * @param ListingParameters $parameters
     *
     * @return BackupCollection[]
     */
    public function findElementsBy(ListingParameters $parameters): array
    {
        $qb = $this->createQueryBuilder('collection');
        $this->appendSearchParameters($parameters, $qb);

        if ($parameters->limit >= 1 && $parameters->page >= 1) {
            $qb->setMaxResults($parameters->limit);
            $qb->setFirstResult(($parameters->page - 1) * $parameters->limit);
        }

        $qb->orderBy('collection.creationDate', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ListingParameters $parameters
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMaxResultsCountForFindElementsBy(ListingParameters $parameters): int
    {
        $qb = $this->createQueryBuilder('collection');
        $qb->select('COUNT(collection)');

        $this->appendSearchParameters($parameters, $qb);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function appendSearchParameters(ListingParameters $parameters, QueryBuilder $qb): void
    {
        if ($parameters->getSearchQuery()) {
            $qb->andWhere('collection.id LIKE :searchQuery OR collection.description.value LIKE :searchQuery');
            $qb->setParameter('searchQuery', '%' . $parameters->getSearchQuery() . '%');
        }

        if ($parameters->createdTo) {
            $qb->andWhere('collection.creationDate <= :createdTo');
            $qb->setParameter('createdTo', $parameters->createdTo);
        }

        if ($parameters->createdFrom) {
            $qb->andWhere('collection.creationDate >= :createdFrom');
            $qb->setParameter('createdFrom', $parameters->createdFrom);
        }

        if ($parameters->allowedTokens) {
            $qb->andWhere('collection.allowedTokens', 'token');
            $qb->where('token.id IN :tokens');
            $qb->setParameter('tokens', $parameters->allowedTokens);
        }
    }
}
