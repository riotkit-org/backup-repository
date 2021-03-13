<?php declare(strict_types=1);

namespace App\Domain\Backup\Manager;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Factory\VersionFactory;
use App\Domain\Backup\Repository\StorageRepository;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Service\CollectionRotator;
use App\Domain\Backup\Service\FileUploader;
use App\Domain\Backup\Validation\BackupValidator;
use App\Domain\Backup\Validation\CollectionValidator;

class BackupManager
{
    protected VersionRepository   $versionRepository;
    protected VersionFactory      $versionFactory;
    protected StorageRepository   $storageRepository;
    protected BackupValidator     $versionValidator;
    protected CollectionValidator $collectionValidator;
    protected CollectionRotator   $collectionRotator;
    protected FileUploader        $fileUploader;

    public function __construct(
        VersionRepository   $repository,
        VersionFactory      $factory,
        StorageRepository   $storageRepository,
        BackupValidator     $versionValidator,
        CollectionValidator $collectionValidator,
        CollectionRotator   $collectionRotator,
        FileUploader        $fileUploader
    ) {
        $this->versionRepository   = $repository;
        $this->versionFactory      = $factory;
        $this->storageRepository   = $storageRepository;
        $this->versionValidator    = $versionValidator;
        $this->collectionValidator = $collectionValidator;
        $this->collectionRotator   = $collectionRotator;
        $this->fileUploader        = $fileUploader;
    }

    /**
     * @param BackupCollection $collection
     * @param $fileId
     *
     * @return StoredVersion
     *
     * @throws BackupLogicException
     */
    public function submitNewVersion(BackupCollection $collection, $fileId): StoredVersion
    {
        $storedFile = $this->storageRepository->findById($fileId);

        if (!$storedFile) {
            throw new \LogicException('Cannot submit a backup for a file that does not exist in the storage');
        }

        // create a new version
        $versionedBackupFile = $this->versionFactory->createVersion(
            $storedFile,
            $collection,
            $this->versionRepository->findCollectionVersions($collection)
        );

        // raise an exception in case, when validation did not pass
        $this->versionValidator->validateBeforeAddingBackup($versionedBackupFile);
        $this->collectionValidator->validateBeforeAddingBackup($collection, $versionedBackupFile);

        // or if everything is ok, then make changes persistent
        $this->versionRepository->persist($versionedBackupFile);
        $this->versionRepository->flushAll();

        // after successful upload - rotate the collection
        $currentVersionsState = $versions = $this->versionRepository->findCollectionVersions($collection);
        $this->collectionRotator->rotate($collection, $currentVersionsState);

        return $versionedBackupFile;
    }

    /**
     * @param StoredVersion $version
     * @param BackupCollection $collection
     *
     * @return callable Use this callable to push changes to the database
     *
     * @throws BackupLogicException
     */
    public function deleteVersion(StoredVersion $version, BackupCollection $collection): callable
    {
        $this->versionValidator->validateBeforeDeletingBackup($version, $collection);
        $this->versionRepository->delete($version);

        return function () use ($version) {
            $this->versionRepository->flushAll();
            $this->fileUploader->deletePreviouslyUploaded($version->getFile()->getFilename());
        };
    }

    public function flushAll(): void
    {
        $this->versionRepository->flushAll();
        $this->storageRepository->flushAll();
    }
}
