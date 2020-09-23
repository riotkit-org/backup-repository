<?php declare(strict_types=1);

namespace App\Domain\Common;

final class Http
{
    public const HTTP_OK = 200;
    public const HTTP_CREATED    = 201;
    public const HTTP_ACCEPTED   = 202;
    public const HTTP_NOT_MODIFIED = 304;
    public const HTTP_MAX_OK_CODE = 399;

    public const HTTP_STREAM_PARTIAL_CONTENT = 206;

    public const HTTP_INVALID_REQUEST        = 401;
    public const HTTP_ACCESS_DENIED          = 403;
    public const HTTP_INVALID_STREAM_RANGE   = 416;
    public const HTTP_NOT_FOUND               = 404;

    public const HTTP_SERVER_ERROR           = 500;
}
