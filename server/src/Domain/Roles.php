<?php declare(strict_types=1);

namespace App\Domain;

/**
 * List of roles which could be required for a temporary token
 *
 *   There are 2 types of roles:
 *       - GRANT
 *       - RESTRICTION
 *
 *   They are distinguished because, we want to GRANT all rights to administrator for example, excluding RESTRICTION
 *   roles.
 *
 * @codeCoverageIgnore
 */
final class Roles
{
    public const TEST_TOKEN             = 'test-token-full-permissions';
    public const INTERNAL_CONSOLE_TOKEN = 'internal-console-token';

    /** Recognized user in the system */
    public const ROLE_USER                       = 'ROLE_USER';

    //
    //
    // upload/creation
    //
    //

    /** Allows to upload files at all */
    public const ROLE_UPLOAD                      = 'upload.all';

    /** Enforce token tags. In result every uploaded file will have tags specified in token regardless if they were sent in request */
    public const ROLE_UPLOAD_ENFORCE_TOKEN_TAGS   = 'upload.enforce_tags_selected_in_token';

    public const ROLE_UPLOAD_ONLY_ONCE_SUCCESSFUL = 'upload.only_once_successful';

    //
    //
    // authentication and tokens
    //
    //

    /** User can check information about ANY token */
    public const ROLE_LOOKUP_TOKENS                = 'security.authentication_lookup';

    /** User can browse/search for tokens */
    public const ROLE_SEARCH_FOR_TOKENS            = 'security.search_for_tokens';

    /** User can overwrite files */
    public const ROLE_ALLOW_OVERWRITE_FILES        = 'security.overwrite';

    /** User can generate tokens with ANY roles */
    public const ROLE_GENERATE_TOKENS              = 'security.generate_tokens';

    /** User can use technical endpoints to manage the application */
    public const ROLE_USE_TECHNICAL_ENDPOINTS      = 'security.use_technical_endpoints';

    /** User can revoke access for other user */
    public const ROLE_DELETE_USERS                = 'security.delete_users';

    /** Special: Marking - tokens with this marking will not be able to be revoked by non-administrators */
    public const ROLE_ADMINISTRATOR                = 'security.administrator';

    /** Allow to specify token id when creating a token */
    public const ROLE_CREATE_PREDICTABLE_TOKEN_IDS = 'security.create_predictable_token_ids';

    /** Prevent token user from seeing other tokens in listings */
    public const ROLE_CANNOT_SEE_FULL_TOKEN_ID     = 'security.cannot_see_full_token_ids';

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

    /** Allows to download ANY file */
    public const ROLE_BROWSE_ALL_FILES = 'view.any_file';

    /** List files from ANY tag that was requested, else the user can list only files by tags allowed in user profile */
    public const ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG = 'view.files_from_all_tags';

    /** Define that the user can use the listing endpoint (basic usage) */
    public const ROLE_ACCESS_LISTING_ENDPOINT = 'view.can_use_listing_endpoint_at_all';

    /** Can see extra, technical metadata such as storage path in the listing */
    public const ROLE_CAN_SEE_EXTRA_ADMIN_METADATA = 'view.can_see_admin_metadata_in_listing';

    //
    //
    // collections
    //
    //

    /** Allow person creating a new backup collection */
    public const ROLE_COLLECTION_ADD = 'collections.create_new';

    /** Allow to assign a specific id, when creating a collection */
    public const ROLE_COLLECTION_CUSTOM_ID = 'collections.create_new.with_custom_id';

    /** Allow creating backup collections that have no limits on size and length */
    public const ROLE_COLLECTION_ADD_WITH_INFINITE_LIMITS = 'collections.allow_infinite_limits';

    /** Edit collections where token is added as allowed */
    public const ROLE_MODIFY_ALLOWED_COLLECTIONS = 'collections.modify_details_of_allowed_collections';

    /** Allow to modify ALL collections. Collection don't have to allow such token which has this role */
    public const ROLE_COLLECTION_MODIFY_ANY_COLLECTION = 'collections.modify_any_collection_regardless_if_token_was_allowed_by_collection';

    /** Allow to browse any collection regardless of if the user token was allowed by it or not */
    public const ROLE_COLLECTION_VIEW_ANY_COLLECTION = 'collections.view_all_collections';

    /** Can use an endpoint that will allow to browse and search collections? */
    public const ROLE_CAN_USE_LISTING_COLLECTION_ENDPOINT = 'collections.can_use_listing_endpoint';

    /** Manage tokens in the collections where our current token is already added as allowed */
    public const ROLE_CAN_MANAGE_USERS_IN_ALLOWED_COLLECTIONS = 'collections.manage_users_in_allowed_collections';

    /** Delete collections where token is added as allowed */
    public const ROLE_CAN_DELETE_ALLOWED_COLLECTIONS = 'collections.delete_allowed_collections';

    /** Can list tokens in all collections, where our token is added */
    public const ROLE_CAN_LIST_TOKENS_IN_COLLECTION = 'collections.can_list_tokens_in_allowed_collections';

    /** Upload to allowed collections */
    public const ROLE_CAN_UPLOAD_TO_ALLOWED_COLLECTIONS = 'collections.upload_to_allowed_collections';

    /** List versions for collections where the token was added as allowed */
    public const ROLE_LIST_VERSIONS_FOR_ALLOWED_COLLECTIONS = 'collections.list_versions_for_allowed_collections';

    /** Delete versions only from collections where the token was added as allowed */
    public const ROLE_DELETE_VERSIONS_IN_ALLOWED_COLLECTIONS = 'collections.delete_versions_for_allowed_collections';

    /** Download a version blob only from collections where token was added as allowed */
    public const ROLE_FETCH_SINGLE_VERSION_FILE_IN_ALLOWED_COLLECTIONS = 'collections.fetch_single_version_file_in_allowed_collections';

    public const GRANTS_LIST = [
        self::ROLE_USER,
        self::ROLE_UPLOAD,
        self::ROLE_LOOKUP_TOKENS,
        self::ROLE_SEARCH_FOR_TOKENS,
        self::ROLE_ALLOW_OVERWRITE_FILES,
        self::ROLE_GENERATE_TOKENS,
        self::ROLE_USE_TECHNICAL_ENDPOINTS,
        self::ROLE_DELETE_ALL_FILES,
        self::ROLE_BROWSE_ALL_FILES,
        self::ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG,
        self::ROLE_ACCESS_LISTING_ENDPOINT,
        self::ROLE_CAN_SEE_EXTRA_ADMIN_METADATA,
        self::ROLE_DELETE_USERS,
        self::ROLE_ADMINISTRATOR,

        // collections
        self::ROLE_COLLECTION_ADD,
        self::ROLE_COLLECTION_CUSTOM_ID,
        self::ROLE_COLLECTION_ADD_WITH_INFINITE_LIMITS,
        self::ROLE_CAN_DELETE_ALLOWED_COLLECTIONS,
        self::ROLE_CAN_LIST_TOKENS_IN_COLLECTION,
        self::ROLE_COLLECTION_MODIFY_ANY_COLLECTION,
        self::ROLE_MODIFY_ALLOWED_COLLECTIONS,
        self::ROLE_COLLECTION_VIEW_ANY_COLLECTION,
        self::ROLE_CAN_USE_LISTING_COLLECTION_ENDPOINT,
        self::ROLE_CAN_MANAGE_USERS_IN_ALLOWED_COLLECTIONS,
        self::ROLE_CAN_UPLOAD_TO_ALLOWED_COLLECTIONS,
        self::ROLE_LIST_VERSIONS_FOR_ALLOWED_COLLECTIONS,
        self::ROLE_DELETE_VERSIONS_IN_ALLOWED_COLLECTIONS
    ];

    /**
     * List of permissions that could be assigned for GIVEN USER in context of GIVEN COLLECTION (ACL)
     */
    public const PER_BACKUP_COLLECTION_LIST = [
        self::ROLE_CAN_DELETE_ALLOWED_COLLECTIONS,
        self::ROLE_CAN_LIST_TOKENS_IN_COLLECTION,
        self::ROLE_MODIFY_ALLOWED_COLLECTIONS,
        self::ROLE_CAN_USE_LISTING_COLLECTION_ENDPOINT,
        self::ROLE_CAN_MANAGE_USERS_IN_ALLOWED_COLLECTIONS,
        self::ROLE_CAN_UPLOAD_TO_ALLOWED_COLLECTIONS,
        self::ROLE_LIST_VERSIONS_FOR_ALLOWED_COLLECTIONS,
        self::ROLE_DELETE_VERSIONS_IN_ALLOWED_COLLECTIONS
    ];

    public const RESTRICTIONS_LIST = [
        self::ROLE_UPLOAD_ENFORCE_TOKEN_TAGS,
        self::ROLE_UPLOAD_ONLY_ONCE_SUCCESSFUL,
        self::ROLE_CANNOT_SEE_FULL_TOKEN_ID
    ];

    public static function getRolesList(): array
    {
        return \array_merge(self::GRANTS_LIST, self::RESTRICTIONS_LIST);
    }

    /**
     * The test token is available only in APP_ENV=test
     *
     * @param string|null $tokenId
     * @return bool
     */
    public static function isTestToken(?string $tokenId): bool
    {
        return $tokenId === static::TEST_TOKEN;
    }

    /**
     * Internal token is used only in CLI commands
     * Cannot be used within any remote access (eg. via HTTP)
     *
     * @param string|null $tokenId
     * @return bool
     */
    public static function isInternalApplicationToken(?string $tokenId): bool
    {
        return $tokenId === static::INTERNAL_CONSOLE_TOKEN;
    }
}
