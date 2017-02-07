<?php declare(strict_types=1);

namespace Repository;

use Doctrine\ORM\EntityManager;
use Manager\StorageManager;
use Model\Entity\File;
use Repository\Domain\FileRepositoryInterface;

/**
 * @package Repository\Domain
 */
class FileRepository implements FileRepositoryInterface
{
    /**
     * @var StorageManager $storageManager
     */
    private $storageManager;

    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @param StorageManager $manager
     * @param EntityManager  $em
     */
    public function __construct(StorageManager $manager, EntityManager $em)
    {
        $this->storageManager = $manager;
        $this->em             = $em;
    }

    /**
     * @param string $name File name or URL address
     * @return File|null
     */
    public function fetchOneByName(string $name)
    {
        $name = $this->storageManager->getStorageFileName($name);

        return $this->em->getRepository(File::class)
            ->findOneBy(['fileName' => $name]);
    }

    /**
     * @inheritdoc
     */
    public function getFileByContentHash(string $hash)
    {
        return $this->em->getRepository(File::class)
            ->findOneBy(['contentHash' => $hash]);
    }
}