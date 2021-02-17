<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Storage\Entity\StoredFile;

class ManagementSecurityContext
{
    /**
     * @var bool
     */
    private $canDeleteFiles;

    public function __construct(bool $canDeleteFiles)
    {
        $this->canDeleteFiles  = $canDeleteFiles;
    }

    public function canDeleteElement(StoredFile $file): bool
    {
        return $this->canDeleteFiles;
    }
}
