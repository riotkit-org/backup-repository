<?php declare(strict_types=1);

namespace App\Domain\Backup\Exception;

use App\Domain\Common\Exception\AuthenticationException as ExceptionFromCommon;
use App\Domain\Errors;

class AuthenticationException extends ExceptionFromCommon
{
    public static function fromForbiddenTokenListingInCollectionCause()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_CANNOT_LIST_TOKENS_ASSOCIATED_TO_COLLECTION,
            Errors::ERR_PERMISSION_CANNOT_LIST_TOKENS_ASSOCIATED_TO_COLLECTION
        );
    }

    public static function fromCollectionAccessManagementDenied()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_COLLECTION_ACCESS_MANAGEMENT_NO_PERMISSION,
            Errors::ERR_PERMISSION_COLLECTION_ACCESS_MANAGEMENT_NO_PERMISSION
        );
    }

    public static function fromBackupUploadActionDisallowed()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_BACKUP_UPLOAD_ALLOWED,
            Errors::ERR_PERMISSION_NO_BACKUP_UPLOAD_ALLOWED
        );
    }

    public static function fromBackupVersionDeletionDisallowed()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_BACKUP_DELETION_ALLOWED,
            Errors::ERR_PERMISSION_NO_BACKUP_DELETION_ALLOWED
        );
    }

    public static function fromBackupDownloadDisallowed()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_BACKUP_DOWNLOAD_ALLOWED,
            Errors::ERR_PERMISSION_NO_BACKUP_DOWNLOAD_ALLOWED
        );
    }

    public static function fromListingBackupsDenied()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_BACKUP_LISTING_ALLOWED,
            Errors::ERR_PERMISSION_NO_BACKUP_LISTING_ALLOWED
        );
    }

    public static function fromCollectionAccessManagementCannotAssignMoreRoles()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_CANNOT_ASSIGN_MORE_ROLES_THAN_HAVE,
            Errors::ERR_PERMISSION_CANNOT_ASSIGN_MORE_ROLES_THAN_HAVE
        );
    }
}
