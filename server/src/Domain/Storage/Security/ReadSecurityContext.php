<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Storage\Entity\StoredFile;

class ReadSecurityContext
{
    private bool $viewAllProtectedFiles;
    private bool $listAllFilesInAllTags;
    private string $requestPassword;
    private array $allowedTags;
    private bool $canListAnything;
    private bool $canSeeAdminMetadata;

    public function __construct(
        bool $viewAllProtectedFiles,
        bool $listAllFilesInAllTags,
        bool $canListAnything,
        string $requestPassword,
        array $allowedTags,
        bool $canSeeAdminMetadata
    ) {
        $this->viewAllProtectedFiles = $viewAllProtectedFiles;
        $this->listAllFilesInAllTags = $listAllFilesInAllTags;
        $this->canListAnything       = $canListAnything;
        $this->requestPassword       = $requestPassword;
        $this->allowedTags           = $allowedTags;
        $this->canSeeAdminMetadata   = $canSeeAdminMetadata;
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

    public function canListAnything(): bool
    {
        return $this->canListAnything;
    }

    public function canSeeAdminMetadata(): bool
    {
        return $this->canSeeAdminMetadata;
    }
}
