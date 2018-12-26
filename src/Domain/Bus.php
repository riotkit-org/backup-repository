<?php declare(strict_types=1);

namespace App\Domain;

final class Bus
{
    public const STORAGE_GET_FILE_SIZE = 'storage.get_file_size';
    public const STORAGE_UPLOAD        = 'storage.upload';
    public const STORAGE_DELETE        = 'storage.delete';
}
