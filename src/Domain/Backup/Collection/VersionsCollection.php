<?php declare(strict_types=1);

namespace App\Domain\Backup\Collection;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredFile;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Backup\ValueObject\FileSize;
use App\Domain\Backup\ValueObject\Version\VersionNumber;

class VersionsCollection
{
    /**
     * @var StoredVersion
     */
    private $versions = [];

    /**
     * @var callable
     */
    private $fsSizeCheck;

    /**
     * @param StoredVersion[]  $versions
     * @param BackupCollection $collection
     * @param callable         $fsSizeCheck
     */
    public function __construct(array $versions, BackupCollection $collection, callable $fsSizeCheck)
    {
        foreach ($versions as $version) {
            if (!$version->getCollection()->isSameAsCollection($collection)) {
                throw new \LogicException(
                    'VersionsCollection<StoredVersion> cannot handle StoredVersion objects of different BackupCollection'
                );
            }
        }

        $this->fsSizeCheck = $fsSizeCheck;
        $this->versions    = $versions;

        $this->updateInternalOrder();
    }

    public function sumAllVersionsDiskSpace(): FileSize
    {
        $eachVersionSpace = array_map(
            function (StoredVersion $version) {
                return $this->getFileSize($version->getFile()->getFilename())->getValue();
            },
            $this->versions
        );

        return new FileSize(array_sum($eachVersionSpace) . 'b');
    }

    public function getCount(): CollectionLength
    {
        return new CollectionLength(\count($this->versions));
    }

    public function areThereAny(): bool
    {
        return $this->getCount()->isHigherThanInteger(0);
    }

    public function getLast(): ?StoredVersion
    {
        $last = end($this->versions);

        if ($last) {
            return $last;
        }

        return null;
    }

    public function getFirst(): ?StoredVersion
    {
        return $this->versions[0] ?? null;
    }

    public function getNextVersionNumber(): VersionNumber
    {
        if ($this->getLast()) {
            return $this->getLast()->getVersionNumber()->incrementVersion();
        }

        return new VersionNumber(1);
    }

    /**
     * @return StoredVersion[]
     */
    public function getAll(): array
    {
        return $this->versions;
    }

    private function updateInternalOrder(): void
    {
        usort(
            $this->versions,
            function (StoredVersion $first, StoredVersion $second) {
                return $first->getVersionNumber()->getValue() < $second->getVersionNumber()->getValue() ? -1 : 1;
            }
        );
    }

    private function getFileSize(Filename $filename): FileSize
    {
        $callable = $this->fsSizeCheck;

        return $callable($filename);
    }

    public function isThereAnyVersionThatHasFile(StoredFile $file): bool
    {
        foreach ($this->versions as $version) {
            if ($version->getFile()->isSameAs($file)) {
                return true;
            }
        }

        return false;
    }

    public function delete(StoredVersion $version): void
    {
        foreach ($this->versions as $key => $currentVersion) {
            if ($currentVersion->isSameAs($version)) {
                unset($this->versions[$key]);
                return;
            }
        }
    }
}
