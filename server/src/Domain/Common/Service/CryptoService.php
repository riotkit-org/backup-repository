<?php declare(strict_types=1);

namespace App\Domain\Common\Service;

use App\Domain\Common\DTO\CryptoStream;
use App\Domain\Common\Aggregate\CryptoSpecification;
use Psr\Http\Message\StreamInterface;

interface CryptoService
{
    public function decode(StreamInterface $inEncStream, string $iv, CryptoSpecification $spec = null): StreamInterface;

    /**
     * Encodes plain stream $inStream and returns IV (initialization vector) to keep, stream to rewrite
     *
     * @param StreamInterface          $inStream
     * @param CryptoSpecification|null $spec
     *
     * @return CryptoStream
     */
    public function encode(StreamInterface $inStream, CryptoSpecification $spec = null): CryptoStream;

    /**
     * Encrypts a string, outputs as a base64 encoded: IV + content
     * Should be used only for smaller amount of data, as everything is done in memory as a whole
     *
     * @param string      $input
     * @param CryptoSpecification|null $spec
     *
     * @return string
     */
    public function encodeString(string $input, CryptoSpecification $spec = null): string;

    public function decodeString(string $input, CryptoSpecification $spec = null): string;

    public function hashString(string $input): string;
}
