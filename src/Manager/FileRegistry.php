<?php declare(strict_types=1);

namespace Manager;

use Model\Entity\File;
use Exception\Upload\DuplicatedContentException;
use Repository\Domain\FileRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * @package Manager
 */
class FileRegistry
{
    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var string $storagePath
     */
    private $storagePath;

    /**
     * @var $storageManager
     */
    private $storageManager;

    /**
     * @var FileRepositoryInterface $repository
     */
    private $repository;

    /**
     * @param EntityManager           $em
     * @param string                  $storagePath
     * @param StorageManager          $manager
     * @param FileRepositoryInterface $repository
     */
    public function __construct(
        EntityManager $em,
        string $storagePath,
        StorageManager $manager,
        FileRepositoryInterface $repository
    ) {
        $this->em             = $em;
        $this->storagePath    = $storagePath;
        $this->storageManager = $manager;
        $this->repository     = $repository;
    }

    /**
     * @param string $fileName File name or URL address
     * @return bool
     */
    public function existsInRegistry($fileName)
    {
        return $this->repository->fetchOneByName($fileName) instanceof File;
    }

    /**
     * In case of a upload failure
     * allow to delete saved file from the disk
     * (should not be used in other cases)
     *
     * @param string $path
     */
    public function revertUploadedDuplicate(string $path)
    {
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Put a file into the registry
     * after successful save/upload to disk
     *
     * @param string $fileName
     * @param string $mimeType
     *
     * @throws FileNotFoundException
     * @throws DuplicatedContentException
     *
     * @return File
     */
    public function registerByName(string $fileName, string $mimeType)
    {
        $filePath = $this->storageManager->getPathWhereToStoreTheFile($fileName);
        $fileName = $this->storageManager->getFileName($fileName);

        if (!is_file($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $hash      = hash_file('md5', $filePath);
        $duplicate = $this->repository->getFileByContentHash($hash);

        if ($duplicate instanceof File) {
            throw new DuplicatedContentException(
                'There already exists a file with the same content. ' .
                'In this case please abort upload action and remove the file',
                $duplicate
            );
        }

        $file = new File();
        $file->setFileName($fileName);
        $file->setContentHash($hash);
        $file->setDateAdded(new \DateTime());
        $file->setMimeType($mimeType);

        // persist and flush changes
        $this->save($file);

        return $file;
    }

    /**
     * @param File $file
     */
    public function save(File $file)
    {
        $this->em->persist($file);
        $this->em->flush($file);
    }

    /**
     * Delete a file from disk and from the registry
     *
     * @param File $file
     */
    public function deleteFile(File $file)
    {
        $path = $this->storageManager->getPathWhereToStoreTheFile($file->getFileName(), false);

        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        unlink($path);

        $this->em->getRepository(File::class)
            ->createQueryBuilder('f')
            ->delete()
            ->where('f.contentHash = :hash')
            ->setParameter('hash', $file->getContentHash())
            ->getQuery()
                ->execute();
    }
}
