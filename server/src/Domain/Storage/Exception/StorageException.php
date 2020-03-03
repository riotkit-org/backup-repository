<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

class StorageException extends \Exception
{
    public const codes = [
        'file_not_found'                => 7016100,
        'io_perm_error'                 => 7016101,
        'consistency_not_found_on_disk' => 7016102,
        'storage_unavailable'           => 7016103
    ];

    public static function fileNotFoundException(): StorageException
    {
        return new StorageException('File not found in the storage', StorageException::codes['file_not_found']);
    }
}
