<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

class MissingDataFieldsError extends \Exception
{
    public const ERROR_MISSING_SECURE_COPY_KEYS = 90091;

    public static function createMissingSecureCopyKeysError(): MissingDataFieldsError
    {
        return new static(
            'secure_copy_options_incomplete_fill_all_or_nothing',
            self::ERROR_MISSING_SECURE_COPY_KEYS
        );
    }
}
