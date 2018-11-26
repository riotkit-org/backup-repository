<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Mime;

class AnonymousFile extends StoredFile
{
    public static function createFromStoredFile(StoredFile $storedFile): AnonymousFile
    {
        $self = new static();
        $self->setMimeType(new Mime($storedFile->getMimeType()));
        $self->setDateAdded($storedFile->getDateAdded());
        $self->setDateAdded($storedFile->getDateAdded());
        $self->changePassword($storedFile->isPasswordProtected() ? 'some-password' : '');

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
}
