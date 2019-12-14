<?php declare(strict_types=1);

namespace App\Domain\Backup\Validation;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Service\Filesystem;
use App\Domain\Backup\Service\UuidValidator;
use App\Domain\Backup\Settings\BackupSettings;
use App\Domain\Backup\ValueObject\Filesize;
use App\Domain\Common\ValueObject\DiskSpace;

class CollectionValidator
{
    /**
     * @var BackupSettings
     */
    private $settings;

    /**
     * @var VersionRepository
     */
    private $versionRepository;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var UuidValidator
     */
    private $uuidValidator;

    public function __construct(BackupSettings $settings, VersionRepository $versionRepository, Filesystem $fs,
                                UuidValidator $uuidValidator)
    {
        $this->settings          = $settings;
        $this->versionRepository = $versionRepository;
        $this->fs                = $fs;
        $this->uuidValidator     = $uuidValidator;
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    public function validateBeforeCreation(BackupCollection $collection, ?string $customId): void
    {
        $this->validateMaxBackupsCount($collection);
        $this->validateMaxOneVersionSize($collection);
        $this->validateMaxCollectionSize($collection);
        $this->validateCollectionSizeIsHigherThanSingleElementSize($collection);
        $this->validateCollectionSizeHasEnoughSize($collection);
        $this->validateCustomId($customId);
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    public function validateBeforeEditing(BackupCollection $collection): void
    {
        $this->validateMaxBackupsCount($collection);
        $this->validateMaxOneVersionSize($collection);
        $this->validateMaxCollectionSize($collection);
        $this->validateCollectionSizeIsHigherThanSingleElementSize($collection);
        $this->validateCollectionSizeHasEnoughSize($collection);

        $this->validateExistingElementsDoesNotExceedSubmittedLimit($collection, null);
    }

    private function validateCustomId(?string $customId): void
    {
        if ($customId && !$this->uuidValidator->isValid($customId)) {
            throw ValidationException::createFromFieldError(
                'custom_id_is_not_uuid_format',
                'id',
                ValidationException::COLLECTION_ID_INVALID_FORMAT
            );
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    public function validateBeforeDeletion(BackupCollection $collection): void
    {
        $this->validateCollectionShouldBeEmpty($collection);
    }

    /**
     * @param BackupCollection $collection
     * @param StoredVersion $version
     *
     * @throws ValidationException
     */
    public function validateBeforeAddingBackup(BackupCollection $collection, StoredVersion $version): void
    {
        $this->validateExistingElementsDoesNotExceedSubmittedLimit(
            $collection,
            $this->fs->getFileSize($version->getFile()->getFilename())
        );
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

        $maxBytesCollectionCanHandle = $collection->getMaxDiskSpaceCollectionCanAllocate();

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

    /**
     * @param BackupCollection $collection
     * @param \App\Domain\Backup\ValueObject\FileSize
     *
     * @throws ValidationException
     */
    private function validateExistingElementsDoesNotExceedSubmittedLimit(
        BackupCollection $collection,
        ?Filesize $additionalElement
    ): void {
        $existingDiskSpaceSum = $this->versionRepository->findCollectionVersions($collection)->sumAllVersionsDiskSpace();

        if ($additionalElement) {
            $existingDiskSpaceSum = $existingDiskSpaceSum->add($additionalElement);
        }

        if ($existingDiskSpaceSum->isHigherThan($collection->getMaxDiskSpaceCollectionCanAllocate())) {
            throw ValidationException::createFromFieldError(
                'max_collection_size_is_smaller_than_real_sum_of_data_in_collection',
                'maxCollectionSize',
                ValidationException::CODE_COLLECTION_IS_ALREADY_TOO_BIG,
                []
            );
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws ValidationException
     */
    private function validateCollectionShouldBeEmpty(BackupCollection $collection): void
    {
        $versions = $this->versionRepository->findCollectionVersions($collection);

        if ($versions->areThereAny()) {
            throw ValidationException::createFromFieldError(
                'collection_cannot_be_deleted_while_it_contains_not_deleted_versions',
                'collection',
                ValidationException::CODE_COLLECTION_HAS_VERSIONS_INSIDE,
                []
            );
        }
    }
}
