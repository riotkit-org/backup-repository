<?php declare(strict_types=1);

namespace App\Domain\Backup\Validation;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Service\Filesystem;

class BackupValidator
{
    private Filesystem $fs;
    private VersionRepository $versionRepository;

    public function __construct(Filesystem $fs, VersionRepository $versionRepository)
    {
        $this->fs                = $fs;
        $this->versionRepository = $versionRepository;
    }

    /**
     * @param StoredVersion $version
     *
     * @throws BackupLogicException
     */
    public function validateBeforeAddingBackup(StoredVersion $version): void
    {
        $this->validateFileSizeNotExceedsCollectionSingleFileSize($version);
        $this->validateFileSizeNotExceedsCollectionSummaryLimit($version);
        $this->validateMaxCollectionLengthReached($version);
        $this->validateFileIsUnique($version);
    }

    /**
     * @param StoredVersion    $version
     * @param BackupCollection $collection
     *
     * @throws BackupLogicException
     */
    public function validateBeforeDeletingBackup(
        StoredVersion $version,
        BackupCollection $collection
    ): void {
        $this->validateCollectionMatches($version, $collection);
    }

    /**
     * @param StoredVersion $version
     *
     * @throws BackupLogicException
     */
    private function validateFileSizeNotExceedsCollectionSingleFileSize(StoredVersion $version): void
    {
        $max = $version->getCollection()->getMaxOneVersionSize();
        $actual = $this->fs->getFileSize($version->getFile()->getFilename());

        if ($actual->isHigherThan($max)) {
            throw BackupLogicException::fromUploadedFileSizeExceedsLimitCause($max, $actual);
        }
    }

    /**
     * @param StoredVersion $version
     *
     * @throws BackupLogicException
     */
    private function validateFileSizeNotExceedsCollectionSummaryLimit(StoredVersion $version): void
    {
        $max = $version->getCollection()->getMaxDiskSpaceCollectionCanAllocate();
        $actual = $this->versionRepository->findCollectionVersions($version->getCollection())->sumAllVersionsDiskSpace();

        if ($actual->isHigherThan($max)) {
            throw BackupLogicException::fromUploadedFileSizeWouldExceedSummaryCollectionSizeCause($max, $actual);
        }
    }

    /**
     * @param StoredVersion $version
     *
     * @throws BackupLogicException
     */
    private function validateFileIsUnique(StoredVersion $version): void
    {
        $isThisFileAlreadyInTheCollection = $this->versionRepository
            ->findCollectionVersions($version->getCollection())->isThereAnyVersionThatHasFile($version->getFile());

        if ($isThisFileAlreadyInTheCollection) {
            throw BackupLogicException::fromFileNotUniqueCause();
        }
    }

    /**
     * @param StoredVersion $version
     *
     * @throws BackupLogicException
     */
    private function validateMaxCollectionLengthReached(StoredVersion $version): void
    {
        if ($version->getCollection()->getStrategy()->shouldCollectionRotateAutomatically()) {
            return;
        }

        $max = $version->getCollection()->getMaxBackupsCount();
        $actual = $this->versionRepository->findCollectionVersions($version->getCollection())->getCount()->increment();

        if ($actual->isHigherThan($max)) {
            throw BackupLogicException::fromMaxCollectionLengthReached($max, $actual);
        }
    }

    /**
     * @param StoredVersion    $version
     * @param BackupCollection $collection
     *
     * @throws BackupLogicException
     */
    private function validateCollectionMatches(StoredVersion $version, BackupCollection $collection): void
    {
        if (!$version->getCollection() || !$version->getCollection()->isSameAsCollection($collection)) {
            throw BackupLogicException::fromCollectingNotMatchingCause();
        }
    }

    // @todo: Add time-based validation - "You cannot upload next backup now, next closest backup window is at H:i:s Y-m-d"
}
