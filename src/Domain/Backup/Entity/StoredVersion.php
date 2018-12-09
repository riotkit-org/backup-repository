<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\ValueObject\Version\VersionNumber;

class StoredVersion
{
    /**
     * @var string UUID-4
     */
    private $id;

    /**
     * @var BackupCollection
     */
    private $collection;

    /**
     * @var StoredFile
     */
    private $file;

    /**
     * @var VersionNumber
     */
    private $versionNumber;

    /**
     * @var \DateTimeImmutable
     */
    private $creationDate;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCollection(): BackupCollection
    {
        return $this->collection;
    }

    /**
     * @return StoredFile
     */
    public function getFile(): StoredFile
    {
        return $this->file;
    }

    /**
     * @return VersionNumber
     */
    public function getVersionNumber(): VersionNumber
    {
        return $this->versionNumber;
    }
}
