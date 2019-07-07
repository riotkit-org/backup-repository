<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

class FileRetrievalError extends StorageException
{
    public const CODE_UPLOAD_MAX_FILESIZE = 60001;
    public const CODE_POST_MAX_FILESIZE   = 60002;
    public const EMPTY_REQUEST            = 60003;
    public const URL_NOT_FOUND            = 60004;
    public const URL_SERVER_NOT_REACHABLE = 60005;
    public const URL_GENERAL_ERROR        = 60006;
}
