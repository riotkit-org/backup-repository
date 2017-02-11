<?php declare(strict_types=1);

namespace Repository;

use Doctrine\ORM\EntityManager;
use Model\Entity\Tag;
use Repository\Domain\TagRepositoryInterface;

/**
 * @package Repository
 */
class TagRepository implements TagRepositoryInterface
{
    /** @var EntityManager $em */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @inheritdoc
     */
    public function findOneByName(string $tagName)
    {
        return $this->em->getRepository(Tag::class)->findOneBy(['name' => $tagName]);
    }
}
