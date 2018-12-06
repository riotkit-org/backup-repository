<?php declare(strict_types=1);

namespace App\Domain\Backup\Validation;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\BackupException;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Settings\BackupSettings;
use App\Domain\Common\ValueObject\DiskSpace;
use App\Domain\Common\ValueObject\Numeric\PositiveNumber;

class CollectionValidator
{
    /**
     * @var BackupSettings
     */
    private $settings;

    public function __construct(BackupSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    public function validateBeforeCreation(BackupCollection $collection): void
    {
        $this->validateMaxBackupsCount($collection);
        $this->validateMaxOneVersionSize($collection);
        $this->validateMaxCollectionSize($collection);
        $this->validateCollectionSizeIsHigherThanSingleElementSize($collection);
        $this->validateCollectionSizeHasEnoughSize($collection);
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    private function validateMaxBackupsCount(BackupCollection $collection): void
    {
        if ($this->settings->getMaxBackupsCountPerCollection()->isZero()) {
            return;
        }

        if ($collection->getMaxBackupsCount()->isHigherThan($this->settings->getMaxBackupsCountPerCollection())) {
            $max = $this->settings->getMaxBackupsCountPerCollection();

            throw ValidationException::createFromFieldError(
                'max_backups_count_too_many',
                'maxBackupsCount',
                ValidationException::CODE_MAX_BACKUPS_COUNT_EXCEEDED,
                ['max' => $max]
            );
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    private function validateMaxOneVersionSize(BackupCollection $collection): void
    {
        if ($this->settings->getMaxOneBackupVersionSize()->isZero()) {
            return;
        }

        if ($collection->getMaxOneVersionSize()->isHigherThan($this->settings->getMaxOneBackupVersionSize())) {
            $max = $this->settings->getMaxOneBackupVersionSize();

            throw ValidationException::createFromFieldError(
                'max_one_version_size_too_big',
                'maxOneVersionSize',
                ValidationException::CODE_MAX_SINGLE_BACKUP_SIZE_EXCEEDED,
                ['max' => $max->toHumanReadable()]
            );
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    private function validateMaxCollectionSize(BackupCollection $collection): void
    {
        if ($this->settings->getMaxWholeCollectionSize()->isZero()) {
            return;
        }

        if ($collection->getMaxCollectionSize()->isHigherThan($this->settings->getMaxWholeCollectionSize())) {
            $max = $this->settings->getMaxWholeCollectionSize();

            throw ValidationException::createFromFieldError(
                'max_collection_size_too_big',
                'maxCollectionSize',
                ValidationException::CODE_MAX_COLLECTION_SIZE_EXCEEDED,
                ['max' => $max->toHumanReadable()]
            );
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    private function validateCollectionSizeIsHigherThanSingleElementSize(BackupCollection $collection): void
    {
        if ($collection->getMaxOneVersionSize()->isHigherThan($collection->getMaxCollectionSize())) {
            throw ValidationException::createFromFieldError(
                'max_collection_size_is_lower_than_single_element_size',
                'maxCollectionSize',
                ValidationException::CODE_SINGLE_ELEMENT_SIZE_BIGGER_THAN_WHOLE_COLLECTION_SIZE,
                []
            );
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    private function validateCollectionSizeHasEnoughSize(BackupCollection $collection): void
    {
        if ($collection->getMaxCollectionSize()->isZero()
            || $collection->getMaxBackupsCount()->isZero()
            || $collection->getMaxOneVersionSize()->isZero()) {
            return;
        }

        $maxBytesCollectionCanHandle = new DiskSpace(
            ($collection->getMaxOneVersionSize()->getValue() * $collection->getMaxBackupsCount()->getValue()) . 'b'
        );

        if ($maxBytesCollectionCanHandle->isHigherThan($collection->getMaxCollectionSize())) {
            throw ValidationException::createFromFieldError(
                'max_collection_size_will_have_not_enough_space_to_keep_max_number_of_items',
                'maxCollectionSize',
                ValidationException::CODE_SINGLE_ELEMENT_SIZE_BIGGER_THAN_WHOLE_COLLECTION_SIZE,
                [
                    'needsAtLeastValue' => $maxBytesCollectionCanHandle->toHumanReadable(),
                ]
            );
        }
    }
}
