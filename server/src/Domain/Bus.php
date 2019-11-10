<?php declare(strict_types=1);

namespace App\Domain;

/**
 * @codeCoverageIgnore
 */
final class Bus
{
    // calls
    public const STORAGE_GET_FILE_SIZE = 'storage.get_file_size';
    public const STORAGE_UPLOAD        = 'storage.upload';
    public const STORAGE_DELETE        = 'storage.delete';
    public const STORAGE_GET_FILE_URL  = 'storage.get_file_url';

    // events
    public const EVENT_STORAGE_UPLOADED_OK = 'event.storage_uploaded_ok';
}
