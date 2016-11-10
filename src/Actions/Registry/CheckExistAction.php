<?php

namespace Actions\Registry;

use Actions\AbstractBaseAction;
use Manager\FileRegistry;
use Manager\StorageManager;
use Model\Entity\File;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Routing\Router;

/**
 * @package Actions\Registry
 */
class CheckExistAction extends AbstractBaseAction
{
    /**
     * @var FileRegistry
     */
    private $registry;

    /**
     * @var string $fileName
     */
    private $fileName;

    /**
     * @var StorageManager $manager
     */
    private $manager;

    /**
     * @param string $fileName
     */
    public function __construct(string $fileName, StorageManager $manager)
    {
        $this->fileName = $fileName;
        $this->manager  = $manager;
    }

    protected function constructServices()
    {
        $this->registry = $this->getContainer()->offsetGet('manager.file_registry');
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $file = $this->registry->fetchOneByName($this->fileName);

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