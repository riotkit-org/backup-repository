<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Repository;

use App\Infrastructure\Common\Repository\BaseRepository;
use App\Domain\Storage\Entity\Tag;
use App\Domain\Storage\Repository\TagRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagsDoctrineRepository extends BaseRepository implements TagRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, Tag::class, $readOnly);
    }

    /**
     * Find existing tags, and create non-existing, THEN RETURN ALL
     *
     * @param array $names
     *
     * @return array
     */
    public function findOrCreateTagsByNames(array $names): array
    {
        $normalized = array_map([$this, 'normalize'], $names);

        $qb = $this->createQueryBuilder('t');
        $qb->where('LOWER(TRIM(t.name)) in (:tags)')
            ->setParameter('tags', $normalized);

        /**
         * @var Tag[] $fromDb
         */
        $fromDb = $qb->getQuery()->getResult();
        $indexedByName = [];

        foreach ($fromDb as $tag) {
            $indexedByName[$tag->getName()] = $tag;
        }

        foreach ($normalized as $name) {
            if (!isset($indexedByName[$name])) {
                $indexedByName[$name] = $this->createTag($name);
            }
        }

        return $indexedByName;
    }

    private function normalize(string $name): string
    {
        return \strtolower(\trim($name));
    }

    private function createTag(string $name): Tag
    {
        $tag = new Tag();
        $tag->setName($name);

        return $tag;
    }
}
