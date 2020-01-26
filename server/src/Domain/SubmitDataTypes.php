<?php declare(strict_types=1);

namespace App\Domain;

/**
 * @codeCoverageIgnore
 */
final class SubmitDataTypes
{
    public const TYPE_TOKEN           = 'token';
    public const TYPE_FILE            = 'file';
    public const TYPE_COLLECTION      = 'collection';
    public const TYPE_COLLECTION_FILE = 'collection-file';

    public const TYPES = [
        self::TYPE_FILE
    ];
}
