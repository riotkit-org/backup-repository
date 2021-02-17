<?php declare(strict_types=1);

namespace App\Domain\Backup\Validation;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Service\Filesystem;
use App\Domain\Backup\Service\UuidValidator;
use App\Domain\Backup\Settings\BackupSettings;
use App\Domain\Backup\ValueObject\Filesize;

class CollectionValidator
{
    private BackupSettings $settings;
    private VersionRepository $versionRepository;
    private Filesystem $fs;
    private UuidValidator $uuidValidator;

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
     * @throws BackupLogicException
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
     * @throws BackupLogicException
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
            throw BackupLogicException::fromIdNotProperlyFormatted();
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws BackupLogicException
     */
    public function validateBeforeDeletion(BackupCollection $collection): void
    {
        $this->validateCollectionShouldBeEmpty($collection);
    }

    /**
     * @param BackupCollection $collection
     * @param StoredVersion $version
     *
     * @throws BackupLogicException
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
     * @throws BackupLogicException
     */
    private function validateMaxBackupsCount(BackupCollection $collection): void
    {
        if ($this->settings->getMaxBackupsCountPerCollection()->isZero()) {
            return;
        }

        if ($collection->getMaxBackupsCount()->isHigherThan($this->settings->getMaxBackupsCountPerCollection())) {
            throw BackupLogicException::fromMaxBackupsCountReached(
                $this->settings->getMaxBackupsCountPerCollection()
            );
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws BackupLogicException
     */
    private function validateMaxOneVersionSize(BackupCollection $collection): void
    {
        if ($this->settings->getMaxOneBackupVersionSize()->isZero()) {
            return;
        }

        if ($collection->getMaxOneVersionSize()->isHigherThan($this->settings->getMaxOneBackupVersionSize())) {
            $max = $this->settings->getMaxOneBackupVersionSize();

            throw BackupLogicException::fromOneFileTooBigCause($max);
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws BackupLogicException
     */
    private function validateMaxCollectionSize(BackupCollection $collection): void
    {
        if ($this->settings->getMaxWholeCollectionSize()->isZero()) {
            return;
        }

        if ($collection->getMaxCollectionSize()->isHigherThan($this->settings->getMaxWholeCollectionSize())) {
            throw BackupLogicException::fromCollectionSizeTooBig($this->settings->getMaxWholeCollectionSize());
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws BackupLogicException
     */
    private function validateCollectionSizeIsHigherThanSingleElementSize(BackupCollection $collection): void
    {
        if ($collection->getMaxOneVersionSize()->isHigherThan($collection->getMaxCollectionSize())) {
            throw BackupLogicException::fromCollectionSizeBiggerThanSingleElementSize();
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws BackupLogicException
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
            throw BackupLogicException::createFromCollectionTooSmallCause(
                $maxBytesCollectionCanHandle
            );
        }
    }

    /**
     * @param BackupCollection $collection
     * @param null|\App\Domain\Backup\ValueObject\FileSize
     *
     * @throws BackupLogicException
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
            throw BackupLogicException::fromExistingElementsAlreadyExceedingSumOfMaximumSize($existingDiskSpaceSum);
        }
    }

    /**
     * @param BackupCollection $collection
     *
     * @throws BackupLogicException
     */
    private function validateCollectionShouldBeEmpty(BackupCollection $collection): void
    {
        $versions = $this->versionRepository->findCollectionVersions($collection);

        if ($versions->areThereAny()) {
            throw BackupLogicException::fromCollectionShouldBeEmptyWhenDeletingCause();
        }
    }
}
