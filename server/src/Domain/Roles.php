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

    /** Recognized user in the system - default and enforced in Symfony */
    public const ROLE_USER                       = 'ROLE_USER';

    //
    //
    // upload/creation
    //
    //

    /** [Storage] Allows to upload files at all */
    public const PERMISSION_UPLOAD                      = 'upload.all';

    /** [Storage] Enforce user tags. In result every uploaded file will have tags specified in user profile regardless if they were sent in request */
    public const PERMISSION_UPLOAD_ENFORCE_USER_TAGS    = 'upload.enforce_tags_selected_in_user_account';

    /** [Storage] Allow current access token (JWT) to upload only one backup version, then the token will be deactivated */
    public const PERMISSION_UPLOAD_ONLY_ONCE_SUCCESSFUL       = 'upload.only_once_successful';

    //
    //
    // authentication and tokens
    //
    //

    /** [Users] User can check information about ANY user */
    public const PERMISSION_LOOKUP_USERS                = 'security.authentication_lookup';

    /** [Users] User can browse/search for users */
    public const PERMISSION_SEARCH_FOR_USERS            = 'security.search_for_users';

    /** [Users] User can overwrite files */
    public const PERMISSION_ALLOW_OVERWRITE_FILES        = 'security.overwrite';

    /** [Users] User can create user accounts with ANY roles */
    public const PERMISSION_CREATE_UNLIMITED_USER_ACCOUNTS         = 'security.create_unlimited_accounts';

    /** [Users] Allow to specify user id when creating a user profile */
    public const PERMISSION_CREATE_PREDICTABLE_USER_IDS = 'security.create_predictable_user_ids';

    /** [Users] User can revoke access for other user */
    public const PERMISSION_DELETE_USERS                = 'security.delete_users';

    /** [Users/Admin] List application permissions with possibility to limit the list by scope of current access token or user account */
    public const PERMISSION_CAN_LIST_ROLES                   = 'security.list_roles';

    /** [Admin] User can use technical endpoints to manage the application - healthcheck, unlimited read-only metrics dashboard */
    public const PERMISSION_USE_TECHNICAL_ENDPOINTS      = 'security.use_technical_endpoints';

    /** [ADMIN ROLE] Administrator */
    public const PERMISSION_ADMINISTRATOR                = 'security.administrator';

    /** [API/JWT] Browse list of authorized accesses on self account. Does not reveal JWT token used to authenticate */
    public const PERMISSION_CAN_SEE_SELF_USER_ACCESS_TOKENS = 'security.can_see_own_access_tokens';

    /** [API/JWT] Browse list of authorized accesses of all users. Does not reveal JWT token used to authenticate */
    public const PERMISSION_CAN_LIST_ALL_USERS_ACCESS_TOKENS = 'security.can_see_all_users_access_tokens';

    /** [API/JWT] Deactivate own API access tokens (JWT) and logged-in sessions */
    public const PERMISSION_CAN_REVOKE_OWN_ACCESS_TOKEN = 'security.revoke_own_access_tokens';

    /** [API/JWT] Deactivate API access tokens (JWT) and logged-in sessions of other users */
    public const PERMISSION_CAN_REVOKE_TOKENS_OF_OTHER_USERS = 'security.revoke_other_users_access_tokens';

    //
    //
    // browsing
    //
    //

    /** [Storage Administration] Allows to download ANY file using storage technical endpoint (useful for administrators) */
    public const PERMISSION_BROWSE_ALL_FILES = 'admin.view.any_file';

    /** [Storage Administration] List files from ANY tag that was requested, else the user can list only files by tags allowed in user profile */
    public const PERMISSION_BROWSE_LIST_OF_FILES_BY_ANY_TAG = 'admin.view.files_from_all_tags';

    /** [Storage Administration] Define that the user can use the listing endpoint (basic usage) */
    public const PERMISSION_ACCESS_LISTING_ENDPOINT = 'admin.view.can_use_listing_endpoint_at_all';

    /** [Storage Administration] Can see extra, technical metadata such as storage path in the listing */
    public const PERMISSION_CAN_SEE_EXTRA_ADMIN_METADATA = 'admin.view.can_see_admin_metadata_in_listing';

    /** [Metrics / System] Can view system metrics such as how many users, collections were created, how many disk was allocated */
    public const PERMISSION_VIEW_METRICS = 'view.metrics';

    //
    //
    // collections
    //
    //

    /** [Global scope] Allow person to create a new backup collection */
    public const PERMISSION_COLLECTION_ADD = 'collections.create_new';

    /** [Global scope] Allow to assign a specific id, when creating a collection (useful for automation software for provision of predictable elements) */
    public const PERMISSION_COLLECTION_CUSTOM_ID = 'collections.create_new.with_custom_id';

    /** [Global scope] Allow creating backup collections that have no limits on size and length */
    public const PERMISSION_COLLECTION_ADD_WITH_INFINITE_LIMITS = 'collections.allow_infinite_limits';

    /** [Global scope] Modify any collection, regardless of if user is in collection permissions list */
    public const PERMISSION_COLLECTION_MODIFY_ANY_COLLECTION = 'collections.modify_any_collection_regardless_if_user_was_allowed_in_collection';

    /** [Global scope] Allow to browse any collection regardless of if the user was allowed by it or not */
    public const PERMISSION_COLLECTION_VIEW_ANY_COLLECTION = 'collections.view_all_collections';

    /** [Global scope] Can use an endpoint that will allow to browse and search collections? */
    public const PERMISSION_CAN_USE_LISTING_COLLECTION_ENDPOINT = 'collections.can_use_listing_endpoint';

    /** [Collection scope] Edit collection */
    public const PERMISSION_MODIFY_ALLOWED_COLLECTIONS = 'collections.modify_details_of_allowed_collections';

    /** [Collection scope] Manage collection permissions */
    public const PERMISSION_CAN_MANAGE_USERS_IN_ALLOWED_COLLECTIONS = 'collections.manage_users_in_allowed_collections';

    /** [Collection scope] Delete collection */
    public const PERMISSION_CAN_DELETE_ALLOWED_COLLECTIONS = 'collections.delete_allowed_collections';

    /** [Collection scope] List users */
    public const PERMISSION_CAN_LIST_TOKENS_IN_COLLECTION = 'collections.can_list_users_in_allowed_collections';

    /** [Collection scope] Upload a new version */
    public const PERMISSION_CAN_UPLOAD_TO_ALLOWED_COLLECTIONS = 'collections.upload_to_allowed_collections';

    /** [Collection scope] List all versions */
    public const PERMISSION_LIST_VERSIONS_FOR_ALLOWED_COLLECTIONS = 'collections.list_versions_for_allowed_collections';

    /** [Collection scope] DELETE any version */
    public const PERMISSION_DELETE_VERSIONS_IN_ALLOWED_COLLECTIONS = 'collections.delete_versions_for_allowed_collections';

    /** [Collection scope] Download any version */
    public const PERMISSION_FETCH_SINGLE_VERSION_FILE_IN_ALLOWED_COLLECTIONS = 'collections.fetch_single_version_file_in_allowed_collections';

    public const GRANTS_LIST = [
        self::ROLE_USER,
        self::PERMISSION_UPLOAD,
        self::PERMISSION_LOOKUP_USERS,
        self::PERMISSION_SEARCH_FOR_USERS,
        self::PERMISSION_ALLOW_OVERWRITE_FILES,
        self::PERMISSION_CREATE_UNLIMITED_USER_ACCOUNTS,
        self::PERMISSION_CREATE_PREDICTABLE_USER_IDS,
        self::PERMISSION_USE_TECHNICAL_ENDPOINTS,
        self::PERMISSION_BROWSE_ALL_FILES,
        self::PERMISSION_BROWSE_LIST_OF_FILES_BY_ANY_TAG,
        self::PERMISSION_ACCESS_LISTING_ENDPOINT,
        self::PERMISSION_CAN_SEE_EXTRA_ADMIN_METADATA,
        self::PERMISSION_VIEW_METRICS,
        self::PERMISSION_DELETE_USERS,
        self::PERMISSION_CAN_LIST_ROLES,
        self::PERMISSION_ADMINISTRATOR,

        // tokens
        self::PERMISSION_CAN_SEE_SELF_USER_ACCESS_TOKENS,
        self::PERMISSION_CAN_LIST_ALL_USERS_ACCESS_TOKENS,
        self::PERMISSION_CAN_REVOKE_OWN_ACCESS_TOKEN,
        self::PERMISSION_CAN_REVOKE_TOKENS_OF_OTHER_USERS,

        // collections
        self::PERMISSION_COLLECTION_ADD,
        self::PERMISSION_COLLECTION_CUSTOM_ID,
        self::PERMISSION_COLLECTION_ADD_WITH_INFINITE_LIMITS,
        self::PERMISSION_CAN_DELETE_ALLOWED_COLLECTIONS,
        self::PERMISSION_CAN_LIST_TOKENS_IN_COLLECTION,
        self::PERMISSION_COLLECTION_MODIFY_ANY_COLLECTION,
        self::PERMISSION_MODIFY_ALLOWED_COLLECTIONS,
        self::PERMISSION_COLLECTION_VIEW_ANY_COLLECTION,
        self::PERMISSION_CAN_USE_LISTING_COLLECTION_ENDPOINT,
        self::PERMISSION_CAN_MANAGE_USERS_IN_ALLOWED_COLLECTIONS,
        self::PERMISSION_CAN_UPLOAD_TO_ALLOWED_COLLECTIONS,
        self::PERMISSION_LIST_VERSIONS_FOR_ALLOWED_COLLECTIONS,
        self::PERMISSION_DELETE_VERSIONS_IN_ALLOWED_COLLECTIONS,
        self::PERMISSION_FETCH_SINGLE_VERSION_FILE_IN_ALLOWED_COLLECTIONS
    ];

    /**
     * List of permissions that could be assigned for GIVEN USER in context of GIVEN COLLECTION (ACL)
     */
    public const PER_BACKUP_COLLECTION_LIST = [
        self::PERMISSION_CAN_DELETE_ALLOWED_COLLECTIONS,
        self::PERMISSION_CAN_LIST_TOKENS_IN_COLLECTION,
        self::PERMISSION_MODIFY_ALLOWED_COLLECTIONS,
        self::PERMISSION_CAN_MANAGE_USERS_IN_ALLOWED_COLLECTIONS,
        self::PERMISSION_CAN_UPLOAD_TO_ALLOWED_COLLECTIONS,
        self::PERMISSION_LIST_VERSIONS_FOR_ALLOWED_COLLECTIONS,
        self::PERMISSION_DELETE_VERSIONS_IN_ALLOWED_COLLECTIONS,
        self::PERMISSION_FETCH_SINGLE_VERSION_FILE_IN_ALLOWED_COLLECTIONS
    ];

    public const RESTRICTIONS_LIST = [
        self::PERMISSION_UPLOAD_ENFORCE_USER_TAGS,
        self::PERMISSION_UPLOAD_ONLY_ONCE_SUCCESSFUL
    ];

    public static function getRestrictionsList(): array
    {
        return self::RESTRICTIONS_LIST;
    }

    public static function getPermissionsList(): array
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
