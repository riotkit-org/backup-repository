<?php declare(strict_types=1);

namespace App\Domain\Backup\Factory;

use App\Domain\Backup\Collection\VersionsCollection;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredFile;
use App\Domain\Backup\Entity\StoredVersion;

class VersionFactory
{
    public function createVersion(StoredFile $storedFile, BackupCollection $collection, VersionsCollection $versions): StoredVersion
    {
        return StoredVersion::fromInput($storedFile, $collection)
            ->withVersionNumber($versions->getNextVersionNumber());
    }
}
