<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

use App\Domain\Errors;

/**
 * @codeCoverageIgnore
 */
class CommonStorageException extends ApplicationException
{
    public static function fromDiskSpaceFormatParsingErrorCause(\Throwable $previous = null)
    {
        return new static(
            Errors::ERR_MSG_DISK_SPACE_FORMAT_PARSING_ERROR,
            Errors::ERR_DISK_SPACE_FORMAT_PARSING_ERROR,
            $previous
        );
    }

    public static function fromEmptyFilenameCause()
    {
        return new static(
            Errors::ERR_MSG_STORAGE_EMPTY_FILENAME,
            Errors::ERR_STORAGE_EMPTY_FILENAME
        );
    }

    public static function fromNotValidCharactersInFilename()
    {
        return new static(
            Errors::ERR_MSG_STORAGE_INVALID_CHARACTERS_IN_FILENAME,
            Errors::ERR_STORAGE_INVALID_CHARACTERS_IN_FILENAME
        );
    }

    public static function fromInvalidPathFormat(string $value)
    {
        return new static(
            str_replace('{{ actual }}', $value, Errors::ERR_MSG_INVALID_PATH_FORMAT),
            Errors::ERR_INVALID_PATH_FORMAT
        );
    }

    public static function fromInvalidEncodingCause()
    {
        return new static(
            Errors::ERR_MSG_INVALID_REQUEST_BODY_ENCODING,
            Errors::ERR_INVALID_REQUEST_BODY_ENCODING
        );
    }
}
