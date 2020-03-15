<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Exception;

class StorageReadError extends \Exception
{
    public const ERROR_FILE_NOT_FOUND = 404;
    public const ERROR_UNKNOWN        = 50001;

    public static function createStorageNotFoundException(): self
    {
        return new static('Cannot read from storage, probably the file cannot be found', self::ERROR_FILE_NOT_FOUND);
    }

    public static function createStorageUnknownError(string $details): self
    {
        return new static('Unknown storage error. Details: ' . $details, self::ERROR_UNKNOWN);
    }
}
