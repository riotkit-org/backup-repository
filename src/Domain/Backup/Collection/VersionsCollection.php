<?php declare(strict_types=1);

namespace App\Domain\Backup\Collection;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Backup\ValueObject\FileSize;

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
                return $this->getFileSize($version->getFile()->getFilename());
            },
            $this->versions
        );

        return new FileSize(array_sum($eachVersionSpace) . 'b');
    }

    public function getCount(): int
    {
        return \count($this->versions);
    }

    private function getFileSize(Filename $filename): ?int
    {
        $callable = $this->fsSizeCheck;

        return $callable($filename);
    }

    public function areThereAny(): bool
    {
        return $this->getCount() > 0;
    }
}
