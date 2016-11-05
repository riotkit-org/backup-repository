<?php

namespace Actions\Registry;

use Actions\AbstractBaseAction;
use Manager\FileRegistry;
use Model\Entity\File;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

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

        return [
            'name' => $file->getFileName(),
            'mime' => $file->getMimeType(),
            'hash' => $file->getContentHash(),
            'date' => $file->getDateAdded()->format('Y-m-d H:i:s'),
        ];
    }
}