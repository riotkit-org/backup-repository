<?php declare(strict_types=1);

namespace Tests;

class Urls
{
    public const URL_TOKEN_GENERATE = '/auth/token/generate';
    public const URL_TOKEN_LOOKUP = '/auth/token/{{ token }}';
    public const URL_TOKEN_DELETE = '/auth/token/{{ token }}';
    public const JOB_CLEAR_EXPIRED_TOKENS = '/jobs/token/expired/clear';
    public const ROLES_LISTING = '/auth/roles';

    public const URL_REPOSITORY_FILE_UPLOAD = '/repository/file/upload';
    public const URL_REPOSITORY_UPLOAD_RAW  = '/repository/file/upload';
    public const URL_REPOSITORY_UPLOAD_BY_URL = '/repository/image/add-by-url';
    public const URL_REPOSITORY_DELETE_FILE   = '/repository/file/{{ fileName }}';

    public const URL_REPOSITORY_LISTING = '/repository';

    public const URL_COLLECTION_CREATE = '/repository/collection';
}
