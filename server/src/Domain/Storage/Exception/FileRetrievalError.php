<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

use App\Domain\Errors;

class FileRetrievalError extends StorageException
{
    public static function fromUploadMaxFileSizeReachedCause()
    {
        return new static(
            Errors::ERR_MSG_STORAGE_REACHED_MAX_FILE_SIZE,
            Errors::ERR_STORAGE_REACHED_MAX_FILE_SIZE
        );
    }

    public static function fromPostMaxSizeReachedCause()
    {
        return new static(
            Errors::ERR_MSG_STORAGE_REACHED_MAX_POST_SIZE,
            Errors::ERR_STORAGE_REACHED_MAX_POST_SIZE
        );
    }

    public static function fromChunkedTransferNotSupported()
    {
        return new static(
            Errors::ERR_MSG_CHUNKED_TRANSFER_NOT_SUPPORTED,
            Errors::ERR_CHUNKED_TRANSFER_NOT_SUPPORTED
        );
    }

    public static function fromEmptyRequestCause()
    {
        return new static(
            Errors::ERR_MSG_STORAGE_EMPTY_REQUEST,
            Errors::ERR_STORAGE_EMPTY_REQUEST
        );
    }

    public function canBeDisplayedPublic(): bool
    {
        return true;
    }

    public function getHttpCode(): int
    {
        return 400;
    }
}
