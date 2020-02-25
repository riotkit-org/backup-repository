<?php declare(strict_types=1);

namespace App\Domain\Storage\Aggregate;

use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\ValueObject\Stream;

class FileRetrievedFromStorage
{
    private StoredFile $storedFile;

    private Stream $stream;

    public function __construct(StoredFile $storedFile, Stream $stream)
    {
        $this->storedFile = $storedFile;
        $this->stream     = $stream;
    }

    public function getStoredFile(): StoredFile
    {
        return $this->storedFile;
    }

    public function getStream(): Stream
    {
        return $this->stream;
    }
}
