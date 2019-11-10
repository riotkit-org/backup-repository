<?php declare(strict_types=1);

namespace App\Domain\Backup\Manager;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Factory\VersionFactory;
use App\Domain\Backup\Repository\StorageRepository;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Service\CollectionRotator;
use App\Domain\Backup\Service\FileUploader;
use App\Domain\Backup\Validation\BackupValidator;
use App\Domain\Backup\Validation\CollectionValidator;

class BackupManager
{
    /**
     * @var VersionRepository
     */
    protected $versionRepository;

    /**
     * @var VersionFactory
     */
    protected $versionFactory;

    /**
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * @var BackupValidator
     */
    protected $versionValidator;

    /**
     * @var CollectionValidator
     */
    protected $collectionValidator;

    /**
     * @var CollectionRotator
     */
    protected $collectionRotator;

    /**
     * @var FileUploader
     */
    protected $fileUploader;

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
     * @throws ValidationException
     */
    public function submitNewVersion(BackupCollection $collection, $fileId): StoredVersion
    {
        $storedFile = $this->storageRepository->findById($fileId);

        if (!$storedFile) {
            throw new \LogicException('Cannot submit a backup for a file that does not exist in the storage');
        }

        $this->collectionRotator->rotate($collection, 1);

        $versionedBackupFile = $this->versionFactory->createVersion(
            $storedFile,
            $collection,
            $this->versionRepository->findCollectionVersions($collection)
        );

        $this->versionValidator->validateBeforeAddingBackup($versionedBackupFile);
        $this->collectionValidator->validateBeforeAddingBackup($collection, $versionedBackupFile);

        $this->versionRepository->persist($versionedBackupFile);

        return $versionedBackupFile;
    }

    /**
     * @param StoredVersion $version
     * @param BackupCollection $collection
     *
     * @return callable Use this callable to push changes to the database
     *
     * @throws ValidationException
     */
    public function deleteVersion(StoredVersion $version, BackupCollection $collection): callable
    {
        $this->versionValidator->validateBeforeDeletingBackup($version, $collection);
        $this->versionRepository->delete($version);

        return function () use ($version) {
            $this->versionRepository->flush($version);
            $this->fileUploader->deletePreviouslyUploaded($version->getFile()->getFilename());
        };
    }

    public function flushAll(): void
    {
        $this->versionRepository->flushAll();
        $this->storageRepository->flushAll();
    }
}
