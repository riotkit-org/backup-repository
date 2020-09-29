<?php declare(strict_types=1);

namespace App\Domain;

final class Errors
{
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
}
