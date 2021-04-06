<?php declare(strict_types=1);

namespace App\Domain\Backup\Exception;

use App\Domain\Backup\ValueObject\Collection\BackupSize;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Collection\CollectionSize;
use App\Domain\Backup\ValueObject\FileSize;
use App\Domain\Common\ValueObject\DiskSpace;
use App\Domain\Common\ValueObject\Numeric\PositiveNumber;
use App\Domain\Errors;

/**
 * Represents user visible exceptions
 */
class BackupLogicException extends BackupException
{
    /**
     * @param null|\Throwable $previous
     *
     * @return static
     */
    public static function fromDuplicatedIdCause(\Throwable $previous = null)
    {
        return new static(
            Errors::ERR_MSG_COLLECTION_ID_EXISTS,
            Errors::ERR_COLLECTION_ID_EXISTS,
            $previous
        );
    }

    public static function fromIdNotProperlyFormatted()
    {
        return new static(
            Errors::ERR_MSG_COLLECTION_ID_INVALID_FORMAT,
            Errors::ERR_COLLECTION_ID_INVALID_FORMAT
        );
    }

    public static function fromMaxBackupsCountReached(CollectionLength $maximum)
    {
        return new static(
            str_replace('{{ max }}', (string) $maximum->getValue(), Errors::ERR_MSG_COLLECTION_MAX_FILES_REACHED),
            Errors::ERR_COLLECTION_MAX_FILES_REACHED
        );
    }

    public static function fromOneFileTooBigCause(BackupSize $maximum)
    {
        return new static(
            str_replace('{{ max }}', $maximum->toHumanReadable(), Errors::ERR_MSG_COLLECTION_MAX_FILE_SIZE_REACHED),
            Errors::ERR_COLLECTION_MAX_FILE_SIZE_REACHED
        );
    }

    public static function fromCollectionSizeTooBig(CollectionSize $getMaxWholeCollectionSize)
    {
        return new static(
            str_replace('{{ max }}', $getMaxWholeCollectionSize->toHumanReadable(), Errors::ERR_MSG_MAX_COLLECTION_SIZE_REACHED),
            Errors::ERR_MAX_COLLECTION_SIZE_REACHED
        );
    }

    public static function fromCollectionSizeBiggerThanSingleElementSize()
    {
        return new static(
            Errors::ERR_MSG_COLLECTION_OVERALL_SIZE_SHOULD_BE_BIGGER_THAN_SINGLE_ELEMENT_SIZE,
            Errors::ERR_COLLECTION_OVERALL_SIZE_SHOULD_BE_BIGGER_THAN_SINGLE_ELEMENT_SIZE
        );
    }

    public static function createFromCollectionTooSmallCause(DiskSpace $maxBytesCollectionCanHandle)
    {
        return new static(
            str_replace('{{ required }}', $maxBytesCollectionCanHandle->toHumanReadable(), Errors::ERR_MSG_COLLECTION_REQUIRES_AT_LEAST_SPACE),
            Errors::ERR_COLLECTION_REQUIRES_AT_LEAST_SPACE
        );
    }

    public static function fromExistingElementsAlreadyExceedingSumOfMaximumSize(DiskSpace $existingDiskSpaceSum)
    {
        return new static(
            str_replace('{{ current }}', $existingDiskSpaceSum->toHumanReadable(), Errors::ERR_MSG_COLLECTION_CURRENT_SIZE_IS_BIGGER_THAN_LIMIT),
            Errors::ERR_COLLECTION_CURRENT_SIZE_IS_BIGGER_THAN_LIMIT
        );
    }

    public static function fromCollectionShouldBeEmptyWhenDeletingCause()
    {
        return new static(
            Errors::ERR_MSG_COLLECTION_SHOULD_BE_EMPTY_BEFORE_DELETION,
            Errors::ERR_COLLECTION_SHOULD_BE_EMPTY_BEFORE_DELETION
        );
    }

    public static function fromUploadedFileSizeExceedsLimitCause(BackupSize $max, FileSize $actual)
    {
        return new static(
            str_replace(
                ['{{ max }}', '{{ actual }}'],
                [$max->toHumanReadable(), $actual->toHumanReadable()],
                Errors::ERR_MSG_UPLOAD_EXCEEDS_SINGLE_FILE_LIMIT
            ),
            Errors::ERR_UPLOAD_EXCEEDS_SINGLE_FILE_LIMIT
        );
    }

    public static function fromUploadedFileSizeWouldExceedSummaryCollectionSizeCause(DiskSpace $max, FileSize $actual)
    {
        return new static(
            str_replace(
                ['{{ max }}', '{{ uploaded }}'],
                [$max->toHumanReadable(), $actual->toHumanReadable()],
                Errors::ERR_MSG_UPLOAD_EXCEEDS_COLLECTION_TOTAL_SIZE
            ),
            Errors::ERR_UPLOAD_EXCEEDS_COLLECTION_TOTAL_SIZE
        );
    }

    /**
     * @return static
     */
    public static function fromFileNotUniqueCause()
    {
        return new static(
            Errors::ERR_MSG_UPLOADED_FILE_NOT_UNIQUE,
            Errors::ERR_UPLOADED_FILE_NOT_UNIQUE
        );
    }

    /**
     * @param CollectionLength $max
     * @param PositiveNumber $actual
     *
     * @return static
     */
    public static function fromMaxCollectionLengthReached(CollectionLength $max, PositiveNumber $actual)
    {
        return new static(
            str_replace(
                ['{{ max }}', '{{ actual }}'],
                [$max->getValue(), $actual->getValue()],
                Errors::ERR_MSG_UPLOAD_MAX_FILES_REACHED
            ),
            Errors::ERR_UPLOAD_MAX_FILES_REACHED
        );
    }

    /**
     * @return static
     */
    public static function fromCollectingNotMatchingCause()
    {
        return new static(
            Errors::ERR_MSG_UPLOADED_FILE_DOES_NOT_MATCH_COLLECTION,
            Errors::ERR_UPLOADED_FILE_DOES_NOT_MATCH_COLLECTION
        );
    }

    public function canBeDisplayedPublic(): bool
    {
        return true;
    }

    public function jsonSerialize(): array
    {
        return [
            'error' => $this->getMessage(),
            'code'  => $this->getCode(),
            'type'  => Errors::TYPE_VALIDATION_ERROR
        ];
    }

    public function getHttpCode(): int
    {
        return 400;
    }
}
