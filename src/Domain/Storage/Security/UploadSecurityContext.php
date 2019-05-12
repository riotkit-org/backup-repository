<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\ValueObject\Filesize;
use App\Domain\Storage\ValueObject\Mime;

class UploadSecurityContext
{
    /**
     * @var array
     */
    private $allowedMimes;

    /**
     * @var bool
     */
    private $isAllowedToUpload;

    /**
     * @var array
     */
    private $allowedTags;

    /**
     * @var bool
     */
    private $allowedToOverwrite;

    /**
     * @var int
     */
    private $maxAllowedFileSize;

    /**
     * @var bool
     */
    private $enforceTokenTags;

    /**
     * @var bool
     */
    private $enforceNoPassword;

    /**
     * @var bool
     */
    private $isAdministrator;

    public function __construct(
        array $allowedMimes,
        array $allowedTags,
        bool $isAllowedToUploadAnything,
        bool $allowedToOverwrite,
        int $maxAllowedFileSize,
        bool $enforceTokenTags,
        bool $enforceNoPassword,
        bool $isAdministrator
    ) {
        $this->allowedMimes = $allowedMimes;
        $this->allowedTags = $allowedTags;
        $this->isAllowedToUpload = $isAllowedToUploadAnything;
        $this->allowedToOverwrite = $allowedToOverwrite;
        $this->maxAllowedFileSize = $maxAllowedFileSize;
        $this->enforceTokenTags   = $enforceTokenTags;
        $this->enforceNoPassword  = $enforceNoPassword;
        $this->isAdministrator    = $isAdministrator;
    }

    public function isMimeAllowed(Mime $mime): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return !$this->allowedMimes || \in_array($mime->getValue(), $this->allowedMimes, true);
    }

    public function isTagAllowed(string $tag): bool
    {
        return !$this->allowedTags || \in_array($tag, $this->allowedTags, true);
    }

    public function isActionAllowed(UploadForm $form, UploadSecurityContext $securityContext): SecurityCheckResult
    {
        if ($form->password && !$securityContext->canSetPassword()) {
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
}
