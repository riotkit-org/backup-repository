<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Common\SharedEntity\User;
use App\Domain\Storage\Entity\StoredFile;

class ReadSecurityContext
{
    private bool   $viewAllProtectedFiles;
    private bool   $listAllFilesInAllTags;
    private array  $allowedTags;
    private bool   $canListAnything;
    private bool   $canSeeAdminMetadata;
    private User   $viewerToken;
    private bool   $internallyAllowedToViewAnyFile;

    public function __construct(
        bool $viewAllProtectedFiles,
        bool $listAllFilesInAllTags,
        bool $canListAnything,
        array $allowedTags,
        bool $canSeeAdminMetadata,
        User $viewerToken,
        bool $internallyAllowedToViewAnyFile
    ) {
        $this->viewAllProtectedFiles          = $viewAllProtectedFiles;
        $this->listAllFilesInAllTags          = $listAllFilesInAllTags;
        $this->canListAnything                = $canListAnything;
        $this->allowedTags                    = $allowedTags;
        $this->canSeeAdminMetadata            = $canSeeAdminMetadata;
        $this->viewerToken                    = $viewerToken;
        $this->internallyAllowedToViewAnyFile = $internallyAllowedToViewAnyFile;
    }

    public function isAbleToViewFile(StoredFile $file): bool
    {
        // internal permission for fetching any files, should be used in calls from other domains
        if ($this->internallyAllowedToViewAnyFile) {
            return true;
        }

        return $this->viewAllProtectedFiles;
    }

    // @todo
    public function canUserSeeFileOnList(StoredFile $file): bool
    {
        return $file->isFileTaggedWithAnyOfThose($this->allowedTags) || $this->listAllFilesInAllTags;
    }

    public function canListAnything(): bool
    {
        return $this->canListAnything;
    }

    // @todo
    public function canSeeAdminMetadata(): bool
    {
        return $this->canSeeAdminMetadata;
    }
}
