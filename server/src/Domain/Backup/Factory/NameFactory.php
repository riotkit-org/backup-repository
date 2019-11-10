<?php declare(strict_types=1);

namespace App\Domain\Backup\Factory;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Backup\ValueObject\Version\VersionNumber;

class NameFactory
{
    /**
     * @var VersionRepository
     */
    private $repository;

    public function __construct(VersionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getNextVersionName(BackupCollection $collection): Filename
    {
        $last = $this->repository->findCollectionVersions($collection)->getLast();
        $nextNumber = $last ? $last->getVersionNumber()->incrementVersion() : new VersionNumber(1);

        return $collection->getFilename()->withSuffix('-v' . $nextNumber->getValue());
    }
}
