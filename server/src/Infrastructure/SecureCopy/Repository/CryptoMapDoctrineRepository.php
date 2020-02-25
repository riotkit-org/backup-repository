<?php declare(strict_types=1);

namespace App\Infrastructure\SecureCopy\Repository;

use App\Domain\SecureCopy\Entity\CryptoMap;
use App\Domain\SecureCopy\Exception\CryptoMapNotFoundError;
use App\Domain\SecureCopy\Repository\CryptoMapRepository;
use App\Infrastructure\Common\Repository\BaseRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class CryptoMapDoctrineRepository extends BaseRepository implements CryptoMapRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, CryptoMap::class, $readOnly);
    }

    public function findPlainTextByHash(string $hash, string $type): string
    {
        $qb = $this->createQueryBuilder('cm');
        $qb->select('cm.plain');
        $qb->where('cm.hash = :hash AND cm.type = :type');
        $qb->setParameters(['hash' => $hash, 'type' => $type]);
        $qb->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            throw new CryptoMapNotFoundError('CryptoMap not found');
        }
    }

    /**
     * @inheritDoc
     */
    public function persist(array $maps): void
    {
        $reduced = $this->reduceToNonExistingOnly($maps);

        foreach ($reduced as $map) {
            $this->getEntityManager()->persist($map);
        }

        $this->getEntityManager()->flush();
    }

    public function flushAll(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * Cut off all already existing CryptoMap objects
     *
     * @param CryptoMap[] $maps
     *
     * @return CryptoMap[]
     */
    private function reduceToNonExistingOnly(array $maps): array
    {
        $qb = $this->createQueryBuilder('cm');
        $qb->select('cm.hash');
        $qb->where('cm.hash IN (:hashes)');
        $qb->setParameters([
            'hashes' => $this->toHashIds($maps)
        ]);

        $existing = $qb->getQuery()->getArrayResult();
        $existing = array_map(function (array $cols) { return $cols['hash']; }, $existing);
        $existing = array_values($existing);

        return array_filter(
            $maps,
            function (CryptoMap $map) use ($existing) {
                return !in_array($map->getHash(), $existing);
            }
        );
    }

    /**
     * @param CryptoMap[] $maps
     *
     * @return string[]
     */
    private function toHashIds(array $maps): array
    {
        return array_values(
            array_map(
                function (CryptoMap $map) {
                    return $map->getHash();
                },
                $maps
            )
        );
    }
}
