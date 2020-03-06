<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Storage\Entity\StoredFile;

class ReadSecurityContext
{
    private bool   $viewAllProtectedFiles;
    private bool   $listAllFilesInAllTags;
    private string $requestPassword;
    private array  $allowedTags;
    private bool   $canListAnything;
    private bool   $canSeeAdminMetadata;
    private Token  $viewerToken;
    private bool   $internallyAllowedToViewAnyFile;

    public function __construct(
        bool $viewAllProtectedFiles,
        bool $listAllFilesInAllTags,
        bool $canListAnything,
        string $requestPassword,
        array $allowedTags,
        bool $canSeeAdminMetadata,
        Token $viewerToken,
        bool $internallyAllowedToViewAnyFile
    ) {
        $this->viewAllProtectedFiles          = $viewAllProtectedFiles;
        $this->listAllFilesInAllTags          = $listAllFilesInAllTags;
        $this->canListAnything                = $canListAnything;
        $this->requestPassword                = $requestPassword;
        $this->allowedTags                    = $allowedTags;
        $this->canSeeAdminMetadata            = $canSeeAdminMetadata;
        $this->viewerToken                    = $viewerToken;
        $this->internallyAllowedToViewAnyFile = $internallyAllowedToViewAnyFile;
    }

    public function isAbleToViewFile(StoredFile $file): bool
    {
        // files admin that can read ANY file
        if ($this->viewAllProtectedFiles) {
            return true;
        }

        // internal permission for fetching any files, should be used in calls from other domains
        if ($this->internallyAllowedToViewAnyFile) {
            return true;
        }

        // only a person, who submitted a file can see it
        if (!$file->isPublic() && !$file->wasSubmittedByTokenId($this->viewerToken->getId())) {
            return false;
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
