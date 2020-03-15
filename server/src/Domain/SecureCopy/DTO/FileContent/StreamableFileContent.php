<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\DTO\FileContent;

use Psr\Http\Message\StreamInterface;

/**
 * @codeCoverageIgnore No logic, no test
 */
class StreamableFileContent
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var StreamInterface $stream
     */
    private $stream;

    public function __construct(string $fileName, StreamInterface $callback)
    {
        $this->fileName = $fileName;
        $this->stream = $callback;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getStream(): ?StreamInterface
    {
        return $this->stream;
    }
}
