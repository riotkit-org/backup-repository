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

    public function __construct(array $allowedMimes, array $allowedTags, bool $isAllowedToUploadAnything, bool $allowedToOverwrite)
    {
        $this->allowedMimes = $allowedMimes;
        $this->allowedTags = $allowedTags;
        $this->isAllowedToUpload = $isAllowedToUploadAnything;
        $this->allowedToOverwrite = $allowedToOverwrite;
    }

    public function isMimeAllowed(Mime $mime): bool
    {
        return !$this->allowedMimes || \in_array($mime->getValue(), $this->allowedMimes, true);
    }

    public function isTagAllowed(string $tag): bool
    {
        return !$this->allowedTags || \in_array($tag, $this->allowedTags, true);
    }

    public function isActionAllowed(UploadForm $form): bool
    {
        foreach ($form->tags as $tag) {
            if (!$this->isTagAllowed($tag)) {
                return false;
            }
        }

        return $this->isAllowedToUpload;
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

    public function getMaximumFileSize(): int
    {
        return 0;
    }
}
