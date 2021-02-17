<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

use App\Domain\Storage\ValueObject\Filename;

class AnonymousFile extends StoredFile
{
    public static function createFromStoredFile(StoredFile $storedFile): AnonymousFile
    {
        $self = new static();
        $self->setDateAdded($storedFile->getDateAdded());
        $self->setDateAdded($storedFile->getDateAdded());

        return $self;
    }

    public function getFilename(): Filename
    {
        return new Filename('anonymous');
    }

    public function getContentHash(): string
    {
        return '';
    }

    /**
     * For anonymous files we do not want to show this status
     *
     * @return bool|null
     */
    public function isUniqueInStorage(): ?bool
    {
        return null;
    }
}
