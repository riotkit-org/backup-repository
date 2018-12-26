<?php declare(strict_types=1);

namespace App\Domain\Common\SharedEntity;

use App\Domain\Common\ValueObject\Filename;

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
            || $file->getFilename()->getValue() === $this->getFilename()->getValue();
    }
}
