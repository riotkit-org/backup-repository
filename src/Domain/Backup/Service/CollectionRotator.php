<?php declare(strict_types=1);

namespace App\Domain\Backup\Service;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Repository\VersionRepository;

class CollectionRotator
{
    /**
     * @var VersionRepository
     */
    private $versionRepository;

    /**
     * @var FileUploader
     */
    private $uploader;

    public function __construct(VersionRepository $versionRepository, FileUploader $uploader)
    {
        $this->versionRepository = $versionRepository;
        $this->uploader          = $uploader;
    }

    /**
     * Rotation means DELETING FIRST ELEMENT when THE COLLECTION IS FULL
     * and we want to add a new element as the last position of it
     *
     * @param BackupCollection $collection
     * @param int              $countOfElementsWillBeAddingRightNow
     */
    public function rotate(BackupCollection $collection, int $countOfElementsWillBeAddingRightNow = 0): void
    {
        if (!$this->shouldRotateNow($collection, $countOfElementsWillBeAddingRightNow)) {
            return;
        }

        $versions = $this->versionRepository->findCollectionVersions($collection);
        $first = $versions->getFirst();

        // nothing to rotate in empty collection
        if (!$first) {
            return;
        }

        $this->uploader->deletePreviouslyUploaded($first->getFile()->getFilename());
        $this->versionRepository->delete($first);
        $this->versionRepository->flush($first);
    }

    private function shouldRotateNow(BackupCollection $collection, int $countOfElementsWillBeAddingRightNow): bool
    {
        if (!$collection->getStrategy()->shouldCollectionRotateAutomatically()) {
            return false;
        }

        $versions = $this->versionRepository->findCollectionVersions($collection);

        $max = $collection->getMaxBackupsCount();
        $actual = $versions->getCount()->addInteger($countOfElementsWillBeAddingRightNow);

        return $actual->isHigherThan($max);
    }
}
