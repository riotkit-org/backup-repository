<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Storage\Entity\StoredFile;

class ReadSecurityContext
{
    /**
     * @var bool
     */
    private $viewAllProtectedFiles;

    /**
     * @var string
     */
    private $requestPassword;

    public function __construct(bool $viewAllProtectedFiles, string $requestPassword)
    {
        $this->viewAllProtectedFiles = $viewAllProtectedFiles;
        $this->requestPassword       = $requestPassword;
    }

    public function isAbleToViewFile(StoredFile $file): bool
    {
        // files admin that can read ANY file
        if ($this->viewAllProtectedFiles) {
            return true;
        }

        // valid password was supplied in the request
        return $file->checkPasswordMatchesWith($this->requestPassword);
    }
}
