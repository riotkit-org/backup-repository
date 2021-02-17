<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

use App\Domain\Common\Response\NormalResponse;
use Psr\Http\Message\StreamInterface;

class FileDownloadResponse extends NormalResponse
{
    /**
     * @var null|StreamInterface
     */
    private ?StreamInterface $stream;

    /**
     * @var callable|null
     */
    private $headers;

    /**
     * @var callable|null
     */
    private $contentFlushCallback;

    /**
     * @param bool                 $status
     * @param string               $message
     * @param int                  $code
     * @param array|null           $headers  Flushes headers (allows to decide if we flush them or not)
     * @param callable|null        $contentFlushCallback  Function that copies our content to output stream
     * @param StreamInterface|null $stream         Raw stream for post-processing eg. encryption
     */
    public function __construct(bool $status, string $message, int $code, array $headers = null,
                                callable $contentFlushCallback = null, StreamInterface $stream = null)
    {
        $this->status               = $status;
        $this->message              = $message;
        $this->httpCode             = $code;
        $this->stream               = $stream;
        $this->headers              = $headers;
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
        return $this->httpCode;
    }

    public function isFlushingFile(): bool
    {
        return $this->contentFlushCallback && $this->stream && $this->headers;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public function jsonSerialize(): array
    {
        return [
            'status'  => $this->status,
            'message' => $this->message
        ];
    }

    /**
     * Dictionary of headers
     *
     * @return array|null
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
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
