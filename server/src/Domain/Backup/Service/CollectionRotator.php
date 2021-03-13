<?php declare(strict_types=1);

namespace App\Domain\Backup\Service;

use App\Domain\Backup\Collection\VersionsCollection;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Repository\VersionRepository;

class CollectionRotator
{
    private VersionRepository $versionRepository;
    private FileUploader $uploader;

    public function __construct(VersionRepository $versionRepository, FileUploader $uploader)
    {
        $this->versionRepository = $versionRepository;
        $this->uploader          = $uploader;
    }

    /**
     * Rotation means deleting older, less important versions in order to make a space
     * for a fresh backup
     *
     * @param BackupCollection   $collection
     * @param VersionsCollection $versions
     */
    public function rotate(BackupCollection $collection, VersionsCollection $versions): void
    {
        if (!$this->shouldRotateNow($collection, $versions)) {
            return;
        }

        $first = $versions->getFirst();

        // nothing to rotate in empty collection
        if (!$first) {
            return;
        }

        // at first unpin from collection
        $this->versionRepository->delete($first);
        $this->versionRepository->flushAll();

        // then delete from registry (and from storage)
        $this->uploader->deletePreviouslyUploaded($first->getFile()->getFilename());
    }

    private function shouldRotateNow(BackupCollection $collection, VersionsCollection $versions): bool
    {
        if (!$collection->getStrategy()->shouldCollectionRotateAutomatically()) {
            return false;
        }

        $max = $collection->getMaxBackupsCount();
        $actual = $versions->getCount();

        return $actual->isHigherThan($max);
    }
}
