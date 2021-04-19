<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Common\SharedEntity\User;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\ValueObject\Filesize;

class UploadSecurityContext
{
    private bool   $isAllowedToUpload;
    private array  $allowedTags;
    private int    $maxAllowedFileSize;
    private bool   $enforceTokenTags;
    private User  $uploaderToken;

    public function __construct(
        array $allowedTags,
        bool $isAllowedToUploadAnything,
        int $maxAllowedFileSize,
        bool $enforceTokenTags,
        User $uploaderToken
    ) {
        $this->allowedTags = $allowedTags;
        $this->isAllowedToUpload = $isAllowedToUploadAnything;
        $this->maxAllowedFileSize = $maxAllowedFileSize;
        $this->enforceTokenTags   = $enforceTokenTags;
        $this->uploaderToken      = $uploaderToken;
    }

    public function isTagAllowed(string $tag): bool
    {
        return !$this->allowedTags || \in_array($tag, $this->allowedTags, true);
    }

    public function isActionAllowed(UploadForm $form): SecurityCheckResult
    {
        foreach ($form->tags as $tag) {
            if (!$this->isTagAllowed($tag)) {
                return new SecurityCheckResult(false, SecurityCheckResult::TAG_NOT_ALLOWED);
            }
        }

        if (!$this->isAllowedToUpload) {
            return new SecurityCheckResult(false, SecurityCheckResult::NOT_ALLOWED_TO_UPLOAD);
        }

        return new SecurityCheckResult(true);
    }

    public function isFileSizeOk(Filesize $fSize): bool
    {
        if ($this->getMaximumFileSize() === 0) {
            return true;
        }

        return $fSize->getValue() < $this->getMaximumFileSize();
    }

    public function getTagsThatShouldBeEnforced(): array
    {
        if (!$this->enforceTokenTags) {
            return [];
        }

        return $this->allowedTags;
    }

    public function getMaximumFileSize(): int
    {
        return $this->maxAllowedFileSize;
    }

    public function getUploaderToken(): User
    {
        return $this->uploaderToken;
    }
}
