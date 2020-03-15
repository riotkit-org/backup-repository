<?php declare(strict_types=1);

namespace App\Domain\Common\DTO;

use Psr\Http\Message\StreamInterface;

class CryptoStream
{
    private string $iv;
    private StreamInterface $stream;

    public function __construct(string $iv, StreamInterface $stream)
    {
        $this->iv = $iv;
        $this->stream = $stream;
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * Hex representation of IV
     *
     * @return string
     */
    public function getIv(): string
    {
        return $this->iv;
    }
}
