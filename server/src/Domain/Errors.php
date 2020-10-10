<?php declare(strict_types=1);

namespace App\Domain;

final class Errors
{
    //
    // Domain and Validation errors
    //

    public const ERR_USER_EXISTS     = 40001;
    public const ERR_MSG_USER_EXISTS = 'User already exists';

//    public const ERR_USER_EMAIL_NOT_UNIQUE     = 40002;
//    public const ERR_MSG_USER_EMAIL_NOT_UNIQUE = 'Selected e-mail address is already taken by someone. Maybe you need to recover your old account?';

    public const ERR_TEXT_FIELD_TOO_LONG       = 40003;
    public const ERR_MSG_TEXT_FIELD_TOO_LONG   = 'Maximum allowed characters exceeded';

    public const ERR_NON_UTF_CHARACTERS        = 40004;
    public const ERR_MSG_NON_UTF_CHARACTERS    = 'Field should contain only UTF-8 encoded characters';

    public const ERR_USER_MAIL_FORMAT_INVALID     = 40005;
    public const ERR_MSG_USER_MAIL_FORMAT_INVALID = 'Invalid e-mail format';

    public const ERR_USER_PASSWORD_TOO_SHORT     = 40006;
    public const ERR_MSG_USER_PASSWORD_TOO_SHORT = 'Password is too short';

    public const ERR_USER_PASSWORD_TOO_LONG      = 40007;
    public const ERR_MSG_USER_PASSWORD_TOO_LONG  = 'Password is too long';

    public const ERR_USER_PASSWORD_TOO_SIMPLE          = 40008;
    public const ERR_MSG_ERR_USER_PASSWORD_TOO_SIMPLE  = 'Password should contain at least one special character';

    public const ERR_USER_PASSWORD_WHITESPACES         = 40009;
    public const ERR_MSG_USER_PASSWORD_WHITESPACES     = 'Password cannot begin or end with a blank character';

    public const ERR_USER_ROLE_INVALID                 = 40010;
    public const ERR_MSG_USER_ROLE_INVALID             = 'Invalid role selected';
//
//    public const ERR_USER_NOT_FOUND_BY_EMAIL     = 40011;
//    public const ERR_MSG_USER_NOT_FOUND_BY_EMAIL = 'User not found';
//
//    public const ERR_INPUT_NOT_NUMERIC     = 40012;
//    public const ERR_MSG_INPUT_NOT_NUMERIC = 'Value is not numeric';
//
//    public const ERR_INPUT_NUMBER_TOO_LOW     = 40013;
//    public const ERR_MSG_INPUT_NUMBER_TOO_LOW = 'Number is too small';
//
//    public const ERR_INPUT_NUMBER_TOO_HIGH     = 40014;
//    public const ERR_MSG_INPUT_NUMBER_TOO_HIGH = 'Number is too high';
//
//    public const ERR_IT_SIZE_INVALID_FORMAT     = 40015;
//    public const ERR_MSG_IT_SIZE_INVALID_FORMAT = 'Invalid human-readable I/O size. Details: {{ msg }}';
//
//    public const ERR_MIME_NOT_RECOGNIZED     = 40016;
//    public const ERR_MSG_MIME_NOT_RECOGNIZED = 'Mime type not recognized';

    public const ERR_SECURITY_INVALID_ROLE     = 40017;
    public const ERR_MSG_SECURITY_INVALID_ROLE = 'Invalid role "{{ role }}"';

//    public const ERR_MAX_COLLECTION_SIZE_NOT_ENOUGH_ESTIMATED     = 40018;
//    public const ERR_MSG_MAX_COLLECTION_SIZE_NOT_ENOUGH_ESTIMATED = 'Maximum collection size must include estimation of all versions together';
//
//    public const ERR_IT_SIZE_CANNOT_BE_NEGATIVE     = 40019;
//    public const ERR_MSG_IT_SIZE_CANNOT_BE_NEGATIVE = 'I/O size cannot be negative';
//
//    public const ERR_STORAGE_NO_DISK_SPACE     = 40020;
//    public const ERR_MSG_STORAGE_NO_DISK_SPACE = 'No enough space on the storage to allocate, please contact the system administrator';

    public const ERR_USERID_FORMAT_INVALID                 = 40021;
    public const ERR_MSG_USERID_FORMAT_INVALID             = 'User ID format invalid, should be a uuidv4 format';

    //
    // Permission errors
    //

    public const ERR_PERMISSION_CANNOT_CREATE_USERS        = 40300;
    public const ERR_MSG_PERMISSION_CANNOT_CREATE_USERS    = 'Current access does not allow to create users';

    public const ERR_REQUEST_PREDICTABLE_ID_FORBIDDEN      = 40301;
    public const ERR_MSG_REQUEST_PREDICTABLE_ID_FORBIDDEN  = 'Current access does not allow setting predictable identifiers for users';

    public const ERR_MSG_REQUEST_READ_ACCESS_DENIED        = 40302;
    public const ERR_REQUEST_READ_ACCESS_DENIED            = 'Got access denied while trying to access the object';

    public const ERR_REQUEST_CANNOT_DELETE                 = 40303;
    public const ERR_MSG_REQUEST_CANNOT_DELETE             = 'No permissions to delete this object';

    public const ERR_LISTING_ENDPOINT_ACCESS_DENIED        = 40304;
    public const ERR_MSG_LISTING_ENDPOINT_ACCESS_DENIED    = 'Cannot access listing endpoint, no enough permissions assigned';

    public const ERR_CANNOT_ASSIGN_CUSTOM_IDS              = 40305;
    public const ERR_MSG_CANNOT_ASSIGN_CUSTOM_IDS          = 'No permissions to assign predictable id';

    public const ERR_PERMISSION_CANNOT_CREATE              = 40306;
    public const ERR_PERMISSION_MSG_CANNOT_CREATE          = 'Current permissions does not allow to create this object';

    public const ERR_PERMISSION_CANNOT_MODIFY              = 40307;
    public const ERR_MSG_PERMISSION_CANNOT_MODIFY          = 'No permissions to modify this object';

    public const ERR_PERMISSION_COLLECTION_ACCESS_MANAGEMENT_NO_PERMISSION     = 40308;
    public const ERR_MSG_PERMISSION_COLLECTION_ACCESS_MANAGEMENT_NO_PERMISSION = 'No permissions to grant and/or revoke access for other users in this collection';

    public const ERR_PERMISSION_CANNOT_LIST_TOKENS_ASSOCIATED_TO_COLLECTION     = 40307;
    public const ERR_MSG_PERMISSION_CANNOT_LIST_TOKENS_ASSOCIATED_TO_COLLECTION = 'Current token does not allow to list tokens of this collection';

    public const ERR_PERMISSION_NO_BACKUP_UPLOAD_ALLOWED     = 40308;
    public const ERR_MSG_PERMISSION_NO_BACKUP_UPLOAD_ALLOWED = 'Current access does not grant you a possibility to upload to this backup collection';

    public const ERR_PERMISSION_NO_BACKUP_DELETION_ALLOWED     = 40309;
    public const ERR_MSG_PERMISSION_NO_BACKUP_DELETION_ALLOWED = 'Current access does not allow you to delete existing backups';

    public const ERR_PERMISSION_NO_BACKUP_DOWNLOAD_ALLOWED     = 40310;
    public const ERR_MSG_PERMISSION_NO_BACKUP_DOWNLOAD_ALLOWED = 'Current access does not allow you to download any backup from this collection';

    public const ERR_PERMISSION_NO_BACKUP_LISTING_ALLOWED     = 40311;
    public const ERR_MSG_PERMISSION_NO_BACKUP_LISTING_ALLOWED = 'Current roles does not grant you a possibility to list files in this backup collection';

    public const ERR_PERMISSION_NO_ACCESS_TO_TECHNICAL_ENDPOINTS     = 40312;
    public const ERR_MSG_PERMISSION_NO_ACCESS_TO_TECHNICAL_ENDPOINTS = 'You don\'t have enough permissions to use technical endpoints';

    public const ERR_PERMISSION_NO_ACCESS_TO_LOOKUP_USER     = 40313;
    public const ERR_MSG_PERMISSION_NO_ACCESS_TO_LOOKUP_USER = 'No permission to lookup a user';

    public const ERR_PERMISSION_NO_ACCESS_TO_SEARCH_USERS     = 40314;
    public const ERR_MSG_PERMISSION_NO_ACCESS_TO_SEARCH_USERS = 'No permission to search for users';

    //
    // Request errors
    //

    public const ERR_REQUEST_CANNOT_PARSE_JSON             = 50000;
    public const ERR_MSG_REQUEST_CANNOT_PARSE_JSON         = 'Cannot parse JSON, details: {{ details }}';

    public const ERR_REQUEST_NO_VALID_USER_FOUND           = 50001;
    public const ERR_MSG_REQUEST_NO_VALID_USER_FOUND       = 'Invalid credentials, cannot find user by id';

    public const ERR_REQUEST_INTERNAL_SERVER_ERROR         = 500;
    public const ERR_MSG_REQUEST_INTERNAL_SERVER_ERROR     = 'Internal server error';

    public const ERR_REQUEST_NOT_FOUND                     = 404;
    public const ERR_MSG_REQUEST_NOT_FOUND                 = 'Route or resource not found';

    public const ERR_REQUEST_ACCESS_DENIED                 = 403;
    public const ERR_MSG_REQUEST_ACCESS_DENIED             = 'Access denied for given route or resource';


    //
    // Types of error responses
    //

    public const TYPE_VALIDATION_ERROR     = 'validation.error';
    public const TYPE_REQUEST_FORMAT_ERROR = 'request.format-error';
    public const TYPE_AUTH_ERROR           = 'request.auth-error';
    public const TYPE_APP_FATAL_ERROR      = 'app.fatal-error';
}
