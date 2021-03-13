<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

use App\Domain\Backup\Exception\BackupException;
use App\Domain\Errors;

class StorageException extends BackupException
{
    /**
     * @return static
     */
    public static function fromFileNotFoundCause(\Throwable $previous = null)
    {
        return new static(
            Errors::ERR_MSG_STORAGE_FILE_NOT_FOUND,
            Errors::ERR_STORAGE_FILE_NOT_FOUND,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function fromPermissionsErrorCause()
    {
        return new static(
            Errors::ERR_MSG_STORAGE_PERMISSION_ERROR,
            Errors::ERR_STORAGE_PERMISSION_ERROR
        );
    }

    /**
     * @param string $filename
     *
     * @return static
     */
    public static function fromFileNotFoundOnDiskButFoundInRegistry(string $filename)
    {
        return new static(
            str_replace('{{ filename }}', $filename, Errors::ERR_MSG_STORAGE_CONSISTENCY_FAILURE_NOT_FOUND_ON_DISK),
            Errors::ERR_STORAGE_CONSISTENCY_FAILURE_NOT_FOUND_ON_DISK
        );
    }

    /**
     * @param \Throwable|null $previous
     *
     * @return static
     */
    public static function fromStorageNotAvailableErrorCause(\Throwable $previous = null)
    {
        return new static(
            str_replace('{{ cause }}', ($previous ? $previous->getMessage() : ''), Errors::ERR_MSG_STORAGE_NOT_AVAILABLE),
            Errors::ERR_STORAGE_NOT_AVAILABLE,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function fromInconsistentWriteCause()
    {
        return new static(
            Errors::ERR_MSG_STORAGE_INCONSISTENT_WRITE,
            Errors::ERR_STORAGE_INCONSISTENT_WRITE,
        );
    }

    public function isFileNotFoundError(): bool
    {
        return $this->getCode() === Errors::ERR_STORAGE_FILE_NOT_FOUND;
    }

    public function getHttpCode(): int
    {
        if ($this->isFileNotFoundError()) {
            return 404;
        }

        return 500;
    }

    public function canBeDisplayedPublic(): bool
    {
        if ($this->isFileNotFoundError()) {
            return true;
        }

        return false;
    }
}
