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
    /**
     * @var Path
     */
    private $path;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Stream
     */
    private $stream;

    public function __construct(Path $path)
    {
        $this->path = $path;
        $this->id = hash('sha256', $path);
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

    public function getId(): string
    {
        return $this->id;
    }
}
