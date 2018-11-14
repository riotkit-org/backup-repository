<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

class FileInfo
{
    /**
     * @var Checksum
     */
    private $checksum;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var Filesize
     */
    private $filesize;

    public function __construct(Checksum $checksum, Mime $mime, Filesize $filesize)
    {
        $this->checksum = $checksum;
        $this->mime = $mime;
        $this->filesize = $filesize;
    }

    public function getValue(): array
    {
        return [$this->checksum, $this->mime];
    }

    /**
     * @return Checksum
     */
    public function getChecksum(): Checksum
    {
        return $this->checksum;
    }

    /**
     * @return Mime
     */
    public function getMime(): Mime
    {
        return $this->mime;
    }

    /**
     * @return Filesize
     */
    public function getFilesize(): Filesize
    {
        return $this->filesize;
    }
}
