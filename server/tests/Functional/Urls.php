<?php declare(strict_types=1);

namespace Tests;

class Urls
{
    public const URL_JWT_AUTH_LOGIN        = '/api/stable/login_check';
    public const URL_JWT_AUTH_TOKEN_CREATE = '/api/stable/auth/token';

    public const URL_USER_CREATE     = '/api/stable/auth/user';
    public const URL_USER_LOOKUP     = '/api/stable/auth/user/{{ userId }}';
    public const URL_TOKEN_DELETE    = '/api/stable/auth/user/{{ userId }}';
    public const URL_TOKEN_SEARCH    = '/api/stable/auth/user?page={{ page }}&limit={{ limit }}&q={{ query }}';
    public const PERMISSIONS_LISTING = '/api/stable/auth/permissions';

    public const URL_REPOSITORY_FILE_UPLOAD      = '/api/stable/repository/file/upload';
    public const URL_REPOSITORY_FETCH_FILE       = '/api/stable/repository/file/{{ fileName }}';

    public const URL_REPOSITORY_LISTING          = '/api/stable/repository';

    public const URL_COLLECTION_CREATE           = '/api/stable/repository/collection';
    public const URL_COLLECTION_UPDATE           = '/api/stable/repository/collection';
    public const URL_COLLECTION_DELETE           = '/api/stable/repository/collection/{{ id }}';
    public const URL_COLLECTION_FETCH            = '/api/stable/repository/collection/{{ id }}';
    public const URL_COLLECTION_LISTING          = '/api/stable/repository/collection';
    public const URL_COLLECTION_UPLOAD           = '/api/stable/repository/collection/{{ collectionId }}/backup';
    public const URL_COLLECTION_LIST_VERSIONS    = '/api/stable/repository/collection/{{ collectionId }}/version';
    public const URL_COLLECTION_DOWNLOAD_VERSION = '/api/stable/repository/collection/{{ collectionId }}/version/{{ version }}';
    public const URL_COLLECTION_DELETE_VERSION   = '/api/stable/repository/collection/{{ collectionId }}/version/{{ version }}';

    public const URL_COLLECTION_GRANT_TOKEN  = '/api/stable/repository/collection/{{ collection }}/access';
    public const URL_COLLECTION_REVOKE_TOKEN = '/api/stable/repository/collection/{{ collection }}/access/{{ user }}';
}
