<?php declare(strict_types=1);

namespace App\Domain\Backup\Settings;

use App\Domain\Backup\ValueObject\Collection\BackupSize;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Collection\CollectionSize;

class BackupSettings
{
    /**
     * @var CollectionLength
     */
    private $maxBackupsCountPerCollection;

    /**
     * @var BackupSize
     */
    private $maxOneBackupVersionSize;

    /**
     * @var CollectionSize
     */
    private $maxWholeCollectionSize;

    public function __construct(
        int $maxBackupsCountPerCollection,
        string $maxOneBackupVersionSize,
        string $maxWholeCollectionSize
    ) {
        $this->maxBackupsCountPerCollection = new CollectionLength($maxBackupsCountPerCollection);
        $this->maxOneBackupVersionSize      = new BackupSize($maxOneBackupVersionSize);
        $this->maxWholeCollectionSize       = new CollectionSize($maxWholeCollectionSize);
    }

    /**
     * @return CollectionLength
     */
    public function getMaxBackupsCountPerCollection(): CollectionLength
    {
        return $this->maxBackupsCountPerCollection;
    }

    /**
     * @return BackupSize
     */
    public function getMaxOneBackupVersionSize(): BackupSize
    {
        return $this->maxOneBackupVersionSize;
    }

    /**
     * @return CollectionSize
     */
    public function getMaxWholeCollectionSize(): CollectionSize
    {
        return $this->maxWholeCollectionSize;
    }
}
