<?php

namespace Actions\Registry;

use Actions\AbstractBaseAction;
use Manager\FileRegistry;
use Model\Entity\File;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Allows to delete a file from the repository
 *
 * @package Actions\Registry
 */
class DeleteAction extends AbstractBaseAction
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
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
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

        $this->registry->deleteFile($file);

        return [
            'hash' => $file->getContentHash(),
        ];
    }
}