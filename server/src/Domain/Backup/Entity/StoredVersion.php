<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\ValueObject\Version\VersionNumber;

class StoredVersion implements \JsonSerializable
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

    public function __construct()
    {
        $this->creationDate = new \DateTimeImmutable();
    }

    /**
     * @param StoredFile $storedFile
     * @param BackupCollection $collection
     *
     * @return static
     */
    public static function fromInput(StoredFile $storedFile, BackupCollection $collection)
    {
        $new = new static();
        $new->file          = $storedFile;
        $new->collection    = $collection;
        $new->versionNumber = new VersionNumber(1);

        return $new;
    }

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

    /**
     * @param VersionNumber $number
     *
     * @return static
     */
    public function withVersionNumber(VersionNumber $number)
    {
        $new = clone $this;
        $new->versionNumber = $number;

        return $new;
    }

    public function isSameAs(StoredVersion $version): bool
    {
        return $this->getId() === $version->getId()
            || $this->getFile()->isSameAs($version->getFile());
    }

    public function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'version'       => $this->getVersionNumber()->getValue(),
            'creation_date' => $this->creationDate,
            'file'          => $this->getFile()
        ];
    }
}
