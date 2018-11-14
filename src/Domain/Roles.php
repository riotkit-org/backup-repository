<?php declare(strict_types=1);

namespace App\Domain;

/**
 * List of roles which could be required for a temporary token
 */
final class Roles
{
    public const ROLE_UPLOAD_IMAGES         = 'upload.images';
    public const ROLE_UPLOAD_FILES          = 'upload.files';
    public const ROLE_UPLOAD_DOCS           = 'upload.documents';
    public const ROLE_UPLOAD_BACKUP         = 'upload.backup';
    public const ROLE_UPLOAD                = 'upload.all';
    public const ROLE_LOOKUP_TOKENS         = 'authentication.lookup';
    public const ROLE_ALLOW_OVERWRITE_FILES = 'security.overwrite';

    public const ROLES_LIST = [
        self::ROLE_UPLOAD_IMAGES,
        self::ROLE_UPLOAD_DOCS,
        self::ROLE_UPLOAD_BACKUP,
        self::ROLE_UPLOAD,
        self::ROLE_LOOKUP_TOKENS,
        self::ROLE_ALLOW_OVERWRITE_FILES
    ];
}
