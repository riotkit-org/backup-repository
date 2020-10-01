<?php declare(strict_types=1);

namespace Tests;

class Urls
{
    public const URL_USER_CREATE  = '/auth/user';
    public const URL_USER_LOOKUP  = '/auth/user/by-id/{{ userId }}';
    public const URL_TOKEN_DELETE = '/auth/user/by-id/{{ userId }}';
    public const URL_TOKEN_SEARCH = '/auth/user/search?page={{ page }}&limit={{ limit }}&q={{ query }}';
    public const ROLES_LISTING    = '/auth/roles';

    public const URL_REPOSITORY_FILE_UPLOAD   = '/repository/file/upload';
    public const URL_REPOSITORY_DELETE_FILE   = '/repository/file/{{ fileName }}';
    public const URL_REPOSITORY_FETCH_FILE    = '/repository/file/{{ fileName }}';
    public const URL_REPOSITORY_READ_ATTRIBUTES = '/repository/file/{{ fileName }}/attributes';

    public const URL_REPOSITORY_LISTING = '/repository';

    public const URL_COLLECTION_CREATE = '/repository/collection';
    public const URL_COLLECTION_UPDATE = '/repository/collection';
    public const URL_COLLECTION_DELETE = '/repository/collection/{{ id }}';
    public const URL_COLLECTION_FETCH  = '/repository/collection/{{ id }}';
    public const URL_COLLECTION_LISTING = '/repository/collection';
    public const URL_COLLECTION_UPLOAD = '/repository/collection/{{ collectionId }}/backup';
    public const URL_COLLECTION_LIST_VERSIONS = '/repository/collection/{{ collectionId }}/version';
    public const URL_COLLECTION_DOWNLOAD_VERSION = '/repository/collection/{{ collectionId }}/version/{{ version }}';
    public const URL_COLLECTION_DELETE_VERSION = '/repository/collection/{{ collectionId }}/version/{{ version }}';

    public const URL_COLLECTION_GRANT_TOKEN = '/repository/collection/{{ collectionId }}/access';
    public const URL_COLLECTION_REVOKE_TOKEN = '/repository/collection/{{ collectionId }}/access/{{ tokenId }}';

    public const URL_SECURE_COPY = '/secure-copy/{{ type }}/list';
    public const URL_SECURE_COPY_RETRIEVE_FILE_METADATA = '/secure-copy/file/{{ file }}/submitdata';
    public const URL_SECURE_COPY_DOWNLOAD_FILE = '/secure-copy/file/{{ file }}/content';
}
