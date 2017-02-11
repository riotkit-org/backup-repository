<?php declare(strict_types=1);

namespace Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Factory\Domain\TagFactoryInterface;
use Manager\Domain\TagManagerInterface;
use Model\Entity\File;
use Model\Entity\Tag;
use Repository\Domain\TagRepositoryInterface;

/**
 * @package Manager
 */
class TagManager implements TagManagerInterface
{
    /**
     * @var TagRepositoryInterface $repository
     */
    private $repository;

    /**
     * @var TagFactoryInterface $factory
     */
    private $factory;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @param TagRepositoryInterface $repository
     * @param TagFactoryInterface $factory
     * @param EntityManager $em
     */
    public function __construct(
        TagRepositoryInterface $repository,
        TagFactoryInterface $factory,
        EntityManager $em
    ) {
        $this->repository = $repository;
        $this->factory    = $factory;
        $this->em         = $em;
    }

    /**
     * @inheritdoc
     */
    public function getNormalizedName(string $tagName): string
    {
       return strtolower(trim($tagName));
    }

    /**
     * @inheritdoc
     */
    public function attachTagToFile(string $tagName, File $file)
    {
        $tagName = $this->getNormalizedName($tagName);
        $tag     = $this->repository->findOneByName($tagName);

        if (!$tag instanceof Tag) {
            $tag = $this->factory->createTag($tagName);
        }

        $file->addTag($tag);
        $this->save($tag, $file);
    }

    /**
     * @inheritdoc
     */
    public function save(Tag $tag, File $file = null)
    {
        if ($file instanceof File) {
            $this->em->persist($file);
        }

        $this->em->persist($tag);
        $this->em->flush();
    }
}
