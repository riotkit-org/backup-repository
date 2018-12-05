<?php declare(strict_types=1);

namespace App\Domain;

/**
 * List of roles which could be required for a temporary token
 */
final class Roles
{
    //
    //
    // upload/creation
    //
    //

    /** Allows to upload images */
    public const ROLE_UPLOAD_IMAGES            = 'upload.images';

    /** Allows to upload documents */
    public const ROLE_UPLOAD_DOCS              = 'upload.documents';

    /** Allows to submit backups */
    public const ROLE_UPLOAD_BACKUP            = 'upload.backup';

    /** Allows to upload ALL types of files regardless of mime type */
    public const ROLE_UPLOAD                   = 'upload.all';

    //
    //
    // authentication and tokens
    //
    //

    /** User can check information about ANY token */
    public const ROLE_LOOKUP_TOKENS           = 'security.authentication_lookup';

    /** User can overwrite files */
    public const ROLE_ALLOW_OVERWRITE_FILES   = 'security.overwrite';

    /** User can generate tokens with ANY roles */
    public const ROLE_GENERATE_TOKENS         = 'security.generate_tokens';

    /** User can use technical endpoints to manage the application */
    public const ROLE_USE_TECHNICAL_ENDPOINTS = 'security.use_technical_endpoints';

    //
    //
    // deletion
    //
    //
    /** Delete files that do not have a password, and password protected without a password */
    public const ROLE_DELETE_ALL_FILES        = 'deletion.all_files_including_protected_and_unprotected';

    //
    //
    // browsing
    //
    //

    /** Allows to download ANY file, even if a file is password protected*/
    public const ROLE_VIEW_ALL_PROTECTED_FILES = 'view.any_file';

    /** List files from ANY tag that was requested, else the user can list only files by tags allowed in token */
    public const ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG = 'view.files_from_all_tags';

    /** Define that the user can use the listing endpoint (basic usage) */
    public const ROLE_ACCESS_LISTING_ENDPOINT = 'view.can_use_listing_endpoint_at_all';

    //
    //
    // collections
    //
    //
    public const ROLE_COLLECTION_ADD = 'collections.create_new';

    public const ROLES_LIST = [
        self::ROLE_UPLOAD_IMAGES,
        self::ROLE_UPLOAD_DOCS,
        self::ROLE_UPLOAD_BACKUP,
        self::ROLE_UPLOAD,
        self::ROLE_LOOKUP_TOKENS,
        self::ROLE_ALLOW_OVERWRITE_FILES,
        self::ROLE_GENERATE_TOKENS,
        self::ROLE_USE_TECHNICAL_ENDPOINTS,
        self::ROLE_DELETE_ALL_FILES,
        self::ROLE_VIEW_ALL_PROTECTED_FILES,
        self::ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG,
        self::ROLE_ACCESS_LISTING_ENDPOINT,
        self::ROLE_COLLECTION_ADD
    ];
}
