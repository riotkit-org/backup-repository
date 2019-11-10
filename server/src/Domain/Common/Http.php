<?php declare(strict_types=1);

namespace App\Domain\Common;

final class Http
{
    public const HTTP_OK = 200;
    public const HTTP_MAX_OK_CODE = 299;

    public const HTTP_STREAM_PARTIAL_CONTENT = 206;
    public const HTTP_INVALID_STREAM_RANGE   = 416;
}
