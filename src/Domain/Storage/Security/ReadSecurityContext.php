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
     * @var bool
     */
    private $listAllFilesInAllTags;

    /**
     * @var string
     */
    private $requestPassword;

    /**
     * @var array
     */
    private $allowedTags;

    public function __construct(
        bool $viewAllProtectedFiles,
        bool $listAllFilesInAllTags,
        string $requestPassword,
        array $allowedTags
    ) {
        $this->viewAllProtectedFiles = $viewAllProtectedFiles;
        $this->listAllFilesInAllTags = $listAllFilesInAllTags;
        $this->requestPassword       = $requestPassword;
        $this->allowedTags           = $allowedTags;
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

    public function canUserSeeFileOnList(StoredFile $file): bool
    {
        $canSeeBecauseOfTag = $file->isFileTaggedWithAnyOfThose($this->allowedTags) || $this->listAllFilesInAllTags;
        $canSeeBecauseOfPassword = $file->checkPasswordMatchesWith($this->requestPassword) || $this->viewAllProtectedFiles;

        return $canSeeBecauseOfPassword && $canSeeBecauseOfTag;
    }
}
