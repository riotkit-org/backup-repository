<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

class ValidationException extends StorageException
{
    public const CODE_MIME_NOT_ALLOWED       = 3000;
    public const CODE_LENGTH_EXCEEDED        = 3001;
    public const CODE_TAG_NOT_ALLOWED        = 3002;
    public const CODE_FILENAME_ALREADY_TAKEN = 3003;

    private const TYPES = [
        self::CODE_MIME_NOT_ALLOWED => 'Mime type not allowed',
        self::CODE_LENGTH_EXCEEDED  => 'File size is too big',
        self::CODE_TAG_NOT_ALLOWED  => 'Tag not allowed to specify',
        self::CODE_FILENAME_ALREADY_TAKEN => 'The filename was already taken by other file',
    ];

    public function getReason(): string
    {
        if (isset(self::TYPES[(int) $this->getCode()])) {
            return self::TYPES[(int) $this->getCode()];
        }

        return 'Unknown validation constraint violation (code: ' .$this->getCode() . ')';
    }
}
