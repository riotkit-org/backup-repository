<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\ValueObject\Filesize;

class UploadSecurityContext
{
    private bool   $isAllowedToUpload;
    private array  $allowedTags;
    private bool   $allowedToOverwrite;
    private int    $maxAllowedFileSize;
    private bool   $enforceTokenTags;
    private bool   $enforceNoPassword;
    private bool   $isAdministrator;
    private bool   $canUploadOnlyOnce;
    private Token  $uploaderToken;

    public function __construct(
        array $allowedTags,
        bool $isAllowedToUploadAnything,
        bool $allowedToOverwrite,
        int $maxAllowedFileSize,
        bool $enforceTokenTags,
        bool $enforceNoPassword,
        bool $isAdministrator,
        bool $canUploadOnlyOnce,
        Token $uploaderToken
    ) {
        $this->allowedTags = $allowedTags;
        $this->isAllowedToUpload = $isAllowedToUploadAnything;
        $this->allowedToOverwrite = $allowedToOverwrite;
        $this->maxAllowedFileSize = $maxAllowedFileSize;
        $this->enforceTokenTags   = $enforceTokenTags;
        $this->enforceNoPassword  = $enforceNoPassword;
        $this->isAdministrator    = $isAdministrator;
        $this->canUploadOnlyOnce  = $canUploadOnlyOnce;
        $this->uploaderToken      = $uploaderToken;
    }

    public function isTagAllowed(string $tag): bool
    {
        return !$this->allowedTags || \in_array($tag, $this->allowedTags, true);
    }

    public function isActionAllowed(UploadForm $form): SecurityCheckResult
    {
        if ($form->password && !$this->canSetPassword()) {
            return new SecurityCheckResult(false, SecurityCheckResult::INVALID_PASSWORD);
        }

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

    public function canOverwriteFile(StoredFile $file, UploadForm $form): bool
    {
        // the user does not want to override the file
        if (!$form->fileOverwrite) {
            return false;
        }

        // token does not allow to overwrite
        if (!$this->allowedToOverwrite) {
            return false;
        }

        // do not allow a regular user to overwrite each anonymously uploaded file that does not have a password
        if (!$this->isAdministrator && $file->checkPasswordMatchesWith('')) {
            return false;
        }

        // password from form must match the old file password to confirm that the authorized person can replace the file
        return $file->checkPasswordMatchesWith($form->password);
    }

    public function getTagsThatShouldBeEnforced(): array
    {
        if (!$this->enforceTokenTags) {
            return [];
        }

        return $this->allowedTags;
    }

    public function canSetPassword(): bool
    {
        return !$this->enforceNoPassword;
    }

    public function getMaximumFileSize(): int
    {
        return $this->maxAllowedFileSize;
    }

    // @todo: Check usage of this, cover with tests
    public function isRestrictedToUploadOnlyOnce(): bool
    {
        return $this->canUploadOnlyOnce;
    }

    public function getUploaderToken(): Token
    {
        return $this->uploaderToken;
    }
}
