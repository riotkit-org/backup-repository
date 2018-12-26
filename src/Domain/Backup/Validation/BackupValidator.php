<?php declare(strict_types=1);

namespace App\Domain\Backup\Validation;

use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Service\Filesystem;

class BackupValidator
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var VersionRepository
     */
    private $versionRepository;

    public function __construct(Filesystem $fs, VersionRepository $versionRepository)
    {
        $this->fs                = $fs;
        $this->versionRepository = $versionRepository;
    }

    /**
     * @param StoredVersion $version
     *
     * @throws ValidationException
     */
    public function validateBeforeAddingBackup(StoredVersion $version): void
    {
        $this->validateFileSizeNotExceedsCollectionSingleFileSize($version);
        $this->validateFileSizeNotExceedsCollectionSummaryLimit($version);
        $this->validateMaxCollectionLengthReached($version);
        $this->validateFileIsUnique($version);
    }

    /**
     * @param StoredVersion $version
     *
     * @throws ValidationException
     */
    private function validateFileSizeNotExceedsCollectionSingleFileSize(StoredVersion $version): void
    {
        $max = $version->getCollection()->getMaxOneVersionSize();
        $actual = $this->fs->getFileSize($version->getFile()->getFilename());

        if ($actual->isHigherThan($max)) {
            throw ValidationException::createFromFieldError(
                'new_version_exceeds_single_element_limit_specified_for_this_collection',
                'file',
                ValidationException::CODE_NEW_VERSION_EXCEEDS_SINGLE_ELEMENT_LIMIT,
                ['max' => $max]
            );
        }
    }

    /**
     * @param StoredVersion $version
     *
     * @throws ValidationException
     */
    private function validateFileSizeNotExceedsCollectionSummaryLimit(StoredVersion $version): void
    {
        $max = $version->getCollection()->getMaxDiskSpaceCollectionCanAllocate();
        $actual = $this->versionRepository->findCollectionVersions($version->getCollection())->sumAllVersionsDiskSpace();

        if ($actual->isHigherThan($max)) {
            throw ValidationException::createFromFieldError(
                'new_version_makes_collection_too_big_on_disk',
                'file',
                ValidationException::CODE_NEW_VERSION_MAKES_COLLECTION_TOO_BIG_ON_DISK,
                ['max' => $max]
            );
        }
    }

    /**
     * @param StoredVersion $version
     *
     * @throws ValidationException
     */
    private function validateFileIsUnique(StoredVersion $version): void
    {
        $isThisFileAlreadyInTheCollection = $this->versionRepository
            ->findCollectionVersions($version->getCollection())->isThereAnyVersionThatHasFile($version->getFile());

        if ($isThisFileAlreadyInTheCollection) {
            throw ValidationException::createFromFieldError(
                'backup_version_uploaded_twice',
                'file',
                ValidationException::CODE_BACKUP_VERSION_DUPLICATED,
                []
            );
        }
    }

    /**
     * @param StoredVersion $version
     *
     * @throws ValidationException
     */
    private function validateMaxCollectionLengthReached(StoredVersion $version): void
    {
        $max = $version->getCollection()->getMaxBackupsCount();
        $actual = $this->versionRepository->findCollectionVersions($version->getCollection())->getCount();

        if ($actual->isHigherThan($max)) {
            throw ValidationException::createFromFieldError(
                'max_backups_count_too_many',
                'maxBackupsCount',
                ValidationException::CODE_MAX_BACKUPS_COUNT_EXCEEDED,
                ['max' => $max]
            );
        }
    }
}
