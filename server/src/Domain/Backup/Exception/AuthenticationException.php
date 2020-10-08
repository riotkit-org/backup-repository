<?php declare(strict_types=1);

namespace App\Domain\Backup\Exception;

use App\Domain\Common\Exception\AuthenticationException as ExceptionFromCommon;
use App\Domain\Errors;

class AuthenticationException extends ExceptionFromCommon
{
    /**
     * @return static
     */
    public static function fromForbiddenTokenListingInCollectionCause()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_CANNOT_LIST_TOKENS_ASSOCIATED_TO_COLLECTION,
            Errors::ERR_PERMISSION_CANNOT_LIST_TOKENS_ASSOCIATED_TO_COLLECTION
        );
    }

    /**
     * @return static
     */
    public static function fromCollectionAccessManagementDenied()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_COLLECTION_ACCESS_MANAGEMENT_NO_PERMISSION,
            Errors::ERR_PERMISSION_COLLECTION_ACCESS_MANAGEMENT_NO_PERMISSION
        );
    }

    /**
     * @return static
     */
    public static function fromBackupUploadActionDisallowed()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_BACKUP_UPLOAD_ALLOWED,
            Errors::ERR_PERMISSION_NO_BACKUP_UPLOAD_ALLOWED
        );
    }

    /**
     * @return static
     */
    public static function fromBackupVersionDeletionDisallowed()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_BACKUP_DELETION_ALLOWED,
            Errors::ERR_PERMISSION_NO_BACKUP_DELETION_ALLOWED
        );
    }

    /**
     * @return static
     */
    public static function fromBackupDownloadDisallowed()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_BACKUP_DOWNLOAD_ALLOWED,
            Errors::ERR_PERMISSION_NO_BACKUP_DOWNLOAD_ALLOWED
        );
    }

    /**
     * @return static
     */
    public static function fromListingBackupsDenied()
    {
        return new static(
            Errors::ERR_MSG_PERMISSION_NO_BACKUP_LISTING_ALLOWED,
            Errors::ERR_PERMISSION_NO_BACKUP_LISTING_ALLOWED
        );
    }
}
