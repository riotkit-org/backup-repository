<?php declare(strict_types=1);

namespace App\Domain;

/**
 * List of roles which could be required for a temporary token
 */
final class Roles
{
    // upload/creation
    public const ROLE_UPLOAD_IMAGES            = 'upload.images';
    public const ROLE_UPLOAD_FILES             = 'upload.files';
    public const ROLE_UPLOAD_DOCS              = 'upload.documents';
    public const ROLE_UPLOAD_BACKUP            = 'upload.backup';
    public const ROLE_UPLOAD                   = 'upload.all';
    public const ROLE_VIEW_ALL_PROTECTED_FILES = 'view.any_file';

    // authentication and tokens
    public const ROLE_LOOKUP_TOKENS           = 'security.authentication_lookup';
    public const ROLE_ALLOW_OVERWRITE_FILES   = 'security.overwrite';
    public const ROLE_GENERATE_TOKENS         = 'security.generate_tokens';
    public const ROLE_USE_TECHNICAL_ENDPOINTS = 'security.use_technical_endpoints';

    // deletion
    public const ROLE_DELETE_ALL_FILES        = 'deletion.all_files_including_protected_and_unprotected';

    public const ROLES_LIST = [
        self::ROLE_UPLOAD_IMAGES,
        self::ROLE_UPLOAD_DOCS,
        self::ROLE_UPLOAD_BACKUP,
        self::ROLE_UPLOAD,
        self::ROLE_LOOKUP_TOKENS,
        self::ROLE_ALLOW_OVERWRITE_FILES,
        self::ROLE_GENERATE_TOKENS,
        self::ROLE_USE_TECHNICAL_ENDPOINTS,
        self::ROLE_VIEW_ALL_PROTECTED_FILES,
        self::ROLE_DELETE_ALL_FILES
    ];
}
