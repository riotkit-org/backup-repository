<?php declare(strict_types=1);

namespace App\Domain\Backup\Response;

class StorageUploadResponse
{
    /**
     * @var int|string|string
     */
    private $id;

    /**
     * @var string
     */
    private $status = '';

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var string
     */
    private $filename;

    public static function createFromArray(array $data): StorageUploadResponse
    {
        $new = new static();
        $new->id        = $data['id'] ?? null;
        $new->status    = $data['status'] ?? '';
        $new->errorCode = $data['error_code'] ?? null;
        $new->filename  = $data['filename'];

        return $new;
    }

    /**
     * @return int|string
     */
    public function getFileId()
    {
        return $this->id;
    }

    public function isSuccess(): bool
    {
        return $this->id !== null;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
