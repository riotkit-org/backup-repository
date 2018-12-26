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
        /**
         * @var StoredVersion|null $latest
         */
        $latest = null;

        foreach ($this->versions as $storedVersion) {
            if (!$latest || $latest->getVersionNumber() < $storedVersion->getVersionNumber()) {
                $latest = $storedVersion;
            }
        }

        return $latest;
    }

    public function getNextVersionNumber(): VersionNumber
    {
        if ($this->getLast()) {
            return $this->getLast()->getVersionNumber()->incrementVersion();
        }

        return new VersionNumber(1);
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
}
