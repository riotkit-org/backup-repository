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

    public const ERR_COLLECTION_ID_EXISTS                  = 40100;
    public const ERR_MSG_COLLECTION_ID_EXISTS              = 'Collection ID is reserved already by other Collection';

    public const ERR_COLLECTION_ID_INVALID_FORMAT          = 40101;
    public const ERR_MSG_COLLECTION_ID_INVALID_FORMAT      = 'Collection ID is not a valid uuidv4 formatted string';

    public const ERR_COLLECTION_MAX_FILES_REACHED          = 40102;
    public const ERR_MSG_COLLECTION_MAX_FILES_REACHED      = 'Maximum count of {{ max }} files reached';

    public const ERR_COLLECTION_MAX_FILE_SIZE_REACHED      = 40103;
    public const ERR_MSG_COLLECTION_MAX_FILE_SIZE_REACHED  = 'Maximum file size of {{ max }} reached';

    public const ERR_MAX_COLLECTION_SIZE_REACHED           = 40104;
    public const ERR_MSG_MAX_COLLECTION_SIZE_REACHED       = 'Maximum collection size cannot exceed {{ max }}';

    public const ERR_COLLECTION_OVERALL_SIZE_SHOULD_BE_BIGGER_THAN_SINGLE_ELEMENT_SIZE     = 40105;
    public const ERR_MSG_COLLECTION_OVERALL_SIZE_SHOULD_BE_BIGGER_THAN_SINGLE_ELEMENT_SIZE = 'Collection size cannot be smaller than single version size';

    public const ERR_COLLECTION_REQUIRES_AT_LEAST_SPACE     = 40106;
    public const ERR_MSG_COLLECTION_REQUIRES_AT_LEAST_SPACE = 'Collection maximum size is too small, requires at least {{ required }}';

    public const ERR_COLLECTION_CURRENT_SIZE_IS_BIGGER_THAN_LIMIT     = 40107;
    public const ERR_MSG_COLLECTION_CURRENT_SIZE_IS_BIGGER_THAN_LIMIT = 'Current collection size {{ current }} is bigger than new limit';

    public const ERR_COLLECTION_SHOULD_BE_EMPTY_BEFORE_DELETION     = 40108;
    public const ERR_MSG_COLLECTION_SHOULD_BE_EMPTY_BEFORE_DELETION = 'Collection should be empty before it will be deleted';

    public const ERR_UPLOAD_EXCEEDS_SINGLE_FILE_LIMIT               = 40109;
    public const ERR_MSG_UPLOAD_EXCEEDS_SINGLE_FILE_LIMIT           = 'Uploaded file exceeds single filesize limit of {{ max }} in the collection, uploaded was {{ actual }}';

    public const ERR_UPLOAD_EXCEEDS_COLLECTION_TOTAL_SIZE           = 41010;
    public const ERR_MSG_UPLOAD_EXCEEDS_COLLECTION_TOTAL_SIZE       = 'Collection maximum size of {{ max }} would be exceeded, if this uploaded file of size {{ uploaded }} would be included';

    public const ERR_UPLOADED_FILE_NOT_UNIQUE     = 41011;
    public const ERR_MSG_UPLOADED_FILE_NOT_UNIQUE = 'Content duplication: Uploaded backup file is of same content as one of previous backups';

    public const ERR_UPLOAD_MAX_FILES_REACHED     = 41012;
    public const ERR_MSG_UPLOAD_MAX_FILES_REACHED = 'Maximum count of files reached in the collection. Any of previous files should be deleted before uploading new';

    public const ERR_UPLOADED_FILE_DOES_NOT_MATCH_COLLECTION     = 41013;
    public const ERR_MSG_UPLOADED_FILE_DOES_NOT_MATCH_COLLECTION = 'File points to a different collection than selected';

    public const ERR_EXPIRATION_DATE_INVALID_FORMAT     = 41014;
    public const ERR_MSG_EXPIRATION_DATE_INVALID_FORMAT = 'Expiration date has invalid date format';

    public const ERR_STORAGE_FILE_NOT_FOUND                        = 42000;
    public const ERR_MSG_STORAGE_FILE_NOT_FOUND                    = 'File not found';

    public const ERR_STORAGE_PERMISSION_ERROR                      = 42001;
    public const ERR_MSG_STORAGE_PERMISSION_ERROR                  = 'Storage permissions error';

    public const ERR_STORAGE_CONSISTENCY_FAILURE_NOT_FOUND_ON_DISK     = 42002;
    public const ERR_MSG_STORAGE_CONSISTENCY_FAILURE_NOT_FOUND_ON_DISK = 'Storage consistency failure - file not found on disk';

    public const ERR_STORAGE_NOT_AVAILABLE                         = 42003;
    public const ERR_MSG_STORAGE_NOT_AVAILABLE                     = 'Storage unavailable. {{ cause }}';

    public const ERR_STORAGE_INCONSISTENT_WRITE                    = 42004;
    public const ERR_MSG_STORAGE_INCONSISTENT_WRITE                = 'Read-write test failed, the read string does not match written';

    public const ERR_STORAGE_READ_ONLY                             = 42005;
    public const ERR_MSG_STORAGE_READ_ONLY                         = 'The storage is read only';

    public const ERR_STORAGE_READ_CONTENT_RANGE_INVALID            = 42006;
    public const ERR_MSG_STORAGE_READ_CONTENT_RANGE_INVALID        = 'Content-Range is invalid';

    public const ERR_STORAGE_REACHED_MAX_FILE_SIZE                 = 42007;
    public const ERR_MSG_STORAGE_REACHED_MAX_FILE_SIZE             = '"upload_max_filesize" in PHP configuration does not allow such big file to be uploaded';

    public const ERR_STORAGE_REACHED_MAX_POST_SIZE                 = 42008;
    public const ERR_MSG_STORAGE_REACHED_MAX_POST_SIZE             = 'Multipart was not parsed by PHP, it can be causedby too low value of "post_max_size" in php.ini';

    public const ERR_STORAGE_EMPTY_REQUEST                         = 42009;
    public const ERR_MSG_STORAGE_EMPTY_REQUEST                     = 'No provided any valid source of file with the HTTP protocol';

    public const ERR_DISK_SPACE_FORMAT_PARSING_ERROR               = 42010;
    public const ERR_MSG_DISK_SPACE_FORMAT_PARSING_ERROR           = 'Disk space format parsing error';

    public const ERR_STORAGE_EMPTY_FILENAME                        = 42011;
    public const ERR_MSG_STORAGE_EMPTY_FILENAME                    = 'Filename cannot be empty';

    public const ERR_STORAGE_INVALID_CHARACTERS_IN_FILENAME        = 42012;
    public const ERR_MSG_STORAGE_INVALID_CHARACTERS_IN_FILENAME    = 'Filename is not valid, please normalize it';

    public const ERR_INVALID_PATH_FORMAT                           = 42013;
    public const ERR_MSG_INVALID_PATH_FORMAT                       = 'Invalid path format: {{ actual }}';

    public const ERR_INVALID_URL                                   = 42014;
    public const ERR_MSG_INVALID_URL                               = 'Invalid URL address: {{ normalized }}';

    public const ERR_INVALID_REQUEST_BODY_ENCODING                 = 42015;
    public const ERR_MSG_INVALID_REQUEST_BODY_ENCODING             = 'Invalid input encoding time';

    public const ERR_NUMBER_CANNOT_BE_NEGATIVE                     = 42016;
    public const ERR_MSG_NUMBER_CANNOT_BE_NEGATIVE                 = 'Number cannot be negative, got {{ actual }}';

    public const ERR_SUM_OF_TWO_NUMBERS_NO_LONGER_GIVES_POSITIVE_NUMBER     = 42017;
    public const ERR_MSG_SUM_OF_TWO_NUMBERS_NO_LONGER_GIVES_POSITIVE_NUMBER = 'The sum of two numbers does not give positive number anymore';

    public const ERR_COMMON_VALUE_INVALID_CHOICE     = 42018;
    public const ERR_MSG_COMMON_VALUE_INVALID_CHOICE = 'Invalid choice "{{ actual }}, possible values: {{ choices }}';

    public const ERR_CHECKSUM_LENGTH_DOES_NOT_MATCH     = 42019;
    public const ERR_MSG_CHECKSUM_LENGTH_DOES_NOT_MATCH = 'Checksum length does not match fixed length of selected algorithm';

    public const ERR_UNSUPPORTED_CHECKSUM_TYPE          = 42020;
    public const ERR_MSG_UNSUPPORTED_CHECKSUM_TYPE      = 'Unsupported checksum type';

    public const ERR_COLLECTION_STRATEGY_INVALID        = 42021;
    public const ERR_MSG_COLLECTION_STRATEGY_INVALID    = 'Invalid collection strategy picked "{{ actual }}". Choices: {{ choices }}';


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

    public const ERR_PERMISSION_CANNOT_ASSIGN_MORE_ROLES_THAN_HAVE     = 40315;
    public const ERR_MSG_PERMISSION_CANNOT_ASSIGN_MORE_ROLES_THAN_HAVE = 'Cannot give roles to other user that current context user does not have';

    //
    // Request errors
    //

    public const ERR_REQUEST_CANNOT_PARSE_JSON             = 50000;
    public const ERR_MSG_REQUEST_CANNOT_PARSE_JSON         = 'Cannot parse JSON, details: {{ details }}';

    public const ERR_REQUEST_NO_VALID_USER_FOUND           = 50001;
    public const ERR_MSG_REQUEST_NO_VALID_USER_FOUND       = 'Invalid credentials, cannot find user by id';

    public const ERR_REQUEST_INPUT_GENERIC_INVALID_FORMAT     = 50002;
    public const ERR_MSG_REQUEST_INPUT_GENERIC_INVALID_FORMAT = 'Invalid format';

    public const ERR_REQUEST_LIMIT_TOO_HIGH     = 50003;
    public const ERR_MSG_REQUEST_LIMIT_TOO_HIGH = 'Limit is too high';

    public const ERR_REQUEST_LIMIT_TOO_LOW      = 50004;
    public const ERR_MSG_REQUEST_LIMIT_TOO_LOW  = 'Limit is too low';

    public const ERR_REQUEST_PAGE_TOO_LOW       = 50005;
    public const ERR_MSG_REQUEST_PAGE_TOO_LOW   = 'Page cannot be negative';

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
    public const TYPE_NOT_FOUND            = 'app.not-found';
}
