<?php declare(strict_types=1);

namespace App\Domain\Common\SharedEntity;

use App\Domain\Common\ValueObject\Filename;
use App\Domain\Common\ValueObject\Checksum;

abstract class StoredFile
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * The real path at storage
     *
     * @var string
     */
    protected $storagePath = '';

    /**
     * @var string
     */
    protected $contentHash = '';

    /**
     * There is no constraint in the database.
     * Tokens could be massively generated, then deleted, we do not want constraints for them
     *
     * @var string Token id
     */
    protected $submittedBy;

    /**
     * @return Filename
     */
    public function getFilename()
    {
        $class = static::getFilenameClass();
        return new $class($this->fileName);
    }

    abstract protected static function getFilenameClass(): string;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function isSameAs(StoredFile $file): bool
    {
        return $file->getId() === $this->getId()
            || $file->getFilename()->getValue() === $this->getFilename()->getValue()
            || $file->getContentHash() === $this->getContentHash();
    }

    public function wasAlreadyStored(): bool
    {
        return $this->contentHash !== '';
    }

    /**
     * @param Checksum $contentHash
     *
     * @return StoredFile
     */
    public function setContentHash(Checksum $contentHash): StoredFile
    {
        $this->contentHash = $contentHash->getValue();
        return $this;
    }

    public function getContentHash(): string
    {
        return $this->contentHash;
    }
}
