<?php

namespace Actions\Registry;

use Actions\AbstractBaseAction;
use Manager\FileRegistry;
use Manager\StorageManager;
use Model\Entity\File;
use Repository\Domain\FileRepositoryInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Routing\Router;

/**
 * @package Actions\Registry
 */
class CheckExistAction extends AbstractBaseAction
{
    /**
     * @var FileRepositoryInterface $repository
     */
    private $repository;

    /**
     * @var string $fileName
     */
    private $fileName;

    /**
     * @var StorageManager $manager
     */
    private $manager;

    /**
     * @param string                  $fileName
     * @param StorageManager          $manager
     * @param FileRepositoryInterface $repository
     */
    public function __construct(string $fileName, StorageManager $manager, FileRepositoryInterface $repository)
    {
        $this->fileName = $fileName;
        $this->manager  = $manager;
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $file = $this->repository->fetchOneByName($this->fileName);

        if (!$file instanceof File) {
            throw new FileNotFoundException('File not found: ' . $this->fileName);
        }

        return [
            'url'  => $this->manager->getFileUrl($file),
            'name' => $file->getFileName(),
            'mime' => $file->getMimeType(),
            'hash' => $file->getContentHash(),
            'date' => $file->getDateAdded()->format('Y-m-d H:i:s'),
        ];
    }
}