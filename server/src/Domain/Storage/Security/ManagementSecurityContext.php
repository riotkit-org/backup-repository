<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Storage\Entity\StoredFile;

class ManagementSecurityContext
{
    /**
     * @var bool
     */
    private $deleteAllFiles;

    /**
     * @var string
     */
    private $requestPassword;

    public function __construct(bool $deleteAllFiles, string $requestPassword)
    {
        $this->deleteAllFiles  = $deleteAllFiles;
        $this->requestPassword = $requestPassword;
    }

    public function canDeleteElement(StoredFile $file): bool
    {
        if ($this->deleteAllFiles) {
            return true;
        }

        // if password was not protected, then we cannot simply delete it as any guest can do same
        if (!$file->isPasswordProtected()) {
            return false;
        }

        return $file->checkPasswordMatchesWith($this->requestPassword);
    }
}
