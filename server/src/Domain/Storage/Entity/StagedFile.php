<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

use App\Domain\Storage\ValueObject\Path;
use App\Domain\Storage\ValueObject\Stream;

/**
 * Represents a temporary file kept in the application's temporary directory
 * (not on destination storage yet)
 */
class StagedFile
{
    private Path $path;
    private string $id;
    private ?Stream $stream = null;

    public function __construct(Path $path)
    {
        $this->path = $path;
        $this->id = hash('sha256', $path->getValue());
    }

    public function getFilePath(): Path
    {
        return $this->path;
    }

    public function openAsStream(): Stream
    {
        if (!$this->stream) {
            $this->stream = new Stream(fopen($this->path->getValue(), 'rb'));
        }

        return $this->stream;
    }

    public function getFilesize(): int
    {
        return filesize($this->getFilePath()->getValue());
    }

    public function getId(): string
    {
        return $this->id;
    }
}
