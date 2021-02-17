<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

use App\Domain\Errors;
use App\Domain\Storage\Exception\StorageException;

/**
 * @codeCoverageIgnore
 */
class ReadOnlyException extends StorageException
{
    public static function fromStorageReadOnlyCause()
    {
        return new static(
            Errors::ERR_STORAGE_READ_ONLY,
            Errors::ERR_MSG_STORAGE_READ_ONLY
        );
    }

    public function canBeDisplayedPublic(): bool
    {
        return true;
    }
}
