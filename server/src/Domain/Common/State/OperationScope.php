<?php declare(strict_types=1);

namespace App\Domain\Common\State;

/**
 * Policy class
 */
class OperationScope
{
    /**
     * @var string[]
     */
    private $allowedMimes;

    /**
     * @var bool
     */
    private $allowOverwriting;

    /**
     * @var int
     */
    private $maxFileSize;

    /**
     * @var bool
     */
    private $allowToUpload;

    public function __construct(array $allowedMimesToUpload, bool $allowOverwriting, int $maxFileSize, bool $allowToUpload)
    {
        $this->allowedMimes     = $allowedMimesToUpload;
        $this->allowOverwriting = $allowOverwriting;
        $this->maxFileSize      = $maxFileSize;
        $this->allowToUpload    = $allowToUpload;
    }

    public function getAllowedMimeTypesToUpload(): array
    {
        if (!$this->allowToUpload) {
            return [];
        }

        return $this->allowedMimes;
    }

    public function isAllowedToOverwriteFiles(): bool
    {
        if (!$this->allowToUpload) {
            return false;
        }

        return $this->allowOverwriting;
    }

    public function getAllowedMaxFileSize(): int
    {
        if (!$this->allowToUpload) {
            return 0;
        }

        return $this->maxFileSize;
    }

    public function isAllowToUpload(): bool
    {
        return $this->allowToUpload;
    }
}
