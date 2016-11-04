<?php

namespace Manager;

use Doctrine\DBAL\Connection;
use Exception\Upload\DuplicatedContentException;
use Model\Entity\File;
use Spot\Locator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * @package Manager
 */
class FileRegistry
{
    /**
     * @var Connection $db
     */
    private $db;

    /**
     * @var string $storagePath
     */
    private $storagePath;

    /**
     * @var $storageManager
     */
    private $storageManager;

    /**
     * @param Locator        $db
     * @param string         $storagePath
     * @param StorageManager $manager
     */
    public function __construct(
        Locator $db,
        string $storagePath,
        StorageManager $manager
    )
    {
        $this->db             = $db;
        $this->storagePath    = $storagePath;
        $this->storageManager = $manager;
    }

    /**
     * @param string $name File name or URL address
     * @return File|null
     */
    public function fetchOneByName($name)
    {
        // @todo: Move this method to repository
        $name = $this->storageManager->getStorageFileName($name);

        return $this->db->mapper(File::class)
            ->first(['fileName' => $name]);
    }

    /**
     * @param string $fileName File name or URL address
     * @return bool
     */
    public function existsInRegistry($fileName)
    {
        return $this->fetchOneByName($fileName) instanceof File;
    }

    /**
     * @param string $hash
     * @return File
     */
    public function getFileByContentHash($hash)
    {
        return $this->db->mapper(File::class)
            ->first(['contentHash' => $hash]);
    }

    /**
     * @param string $path
     */
    public function revertUploadedDuplicate(string $path)
    {
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * @param string $fileName
     * @param string $mimeType
     *
     * @throws FileNotFoundException
     * @throws DuplicatedContentException
     */
    public function registerByName(string $fileName, string $mimeType)
    {
        $filePath = $this->storageManager->getPathWhereToStoreTheFile($fileName);
        $fileName = $this->storageManager->getFileName($fileName);

        if (!is_file($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $hash      = hash_file('md5', $filePath);
        $duplicate = $this->getFileByContentHash($hash);

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
        $this->db->mapper(File::class)->save($file);
    }
}