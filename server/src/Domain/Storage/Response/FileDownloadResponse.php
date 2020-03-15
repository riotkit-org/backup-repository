<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

use Psr\Http\Message\StreamInterface;

class FileDownloadResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $code;

    /**
     * @var null|StreamInterface
     */
    private ?StreamInterface $stream;

    /**
     * @var callable|null
     */
    private $headersFlushCallback;

    /**
     * @var callable|null
     */
    private $contentFlushCallback;

    /**
     * @param string $status
     * @param int $code
     * @param callable|null $headersFlushCallback  Flushes headers (allows to decide if we flush them or not)
     * @param callable|null $contentFlushCallback  Function that copies our content to output stream
     * @param StreamInterface|null $stream         Raw stream for post-processing eg. encryption
     */
    public function __construct(string $status, int $code, callable $headersFlushCallback = null,
                                callable $contentFlushCallback = null, StreamInterface $stream = null)
    {
        $this->status = $status;
        $this->code   = $code;
        $this->stream = $stream;
        $this->headersFlushCallback = $headersFlushCallback;
        $this->contentFlushCallback = $contentFlushCallback;
    }

    public function getResponseStream(): ?StreamInterface
    {
        return $this->stream;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    public function isFlushingFile(): bool
    {
        return $this->contentFlushCallback && $this->stream && $this->headersFlushCallback;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    public function jsonSerialize()
    {
        return [
            'status' => $this->status,
            'code'   => $this->code
        ];
    }

    /**
     * Flush-out the headers
     *
     * Usage: $callback();
     *
     * @return callable|null
     */
    public function getHeadersFlushCallback(): ?callable
    {
        return $this->headersFlushCallback;
    }

    /**
     * Helps to copy streams from $INPUT to $OUTPUT including HTTP Byte Range
     *
     * Usage: $callback($input, $output); - where both arguments are of PHP's "resource" type
     *
     * @return callable|null
     */
    public function getContentFlushCallback(): ?callable
    {
        return $this->contentFlushCallback;
    }
}
