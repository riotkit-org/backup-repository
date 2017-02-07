<?php

namespace Actions\Registry;

use Actions\AbstractBaseAction;
use Manager\FileRegistry;
use Model\Entity\File;
use Repository\Domain\FileRepositoryInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Allows to delete a file from the repository
 *
 * @package Actions\Registry
 */
class DeleteAction extends AbstractBaseAction
{
    /**
     * @var FileRegistry $registry
     */
    private $registry;

    /**
     * @var FileRepositoryInterface $repository
     */
    private $repository;

    /**
     * @var string $fileName
     */
    private $fileName;

    /**
     * @param string $fileName
     * @param FileRepositoryInterface $repository
     * @param FileRegistry $registry
     */
    public function __construct(string $fileName, FileRepositoryInterface $repository, FileRegistry $registry)
    {
        $this->fileName   = $fileName;
        $this->registry   = $registry;
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

        $this->registry->deleteFile($file);

        return [
            'hash' => $file->getContentHash(),
        ];
    }
}