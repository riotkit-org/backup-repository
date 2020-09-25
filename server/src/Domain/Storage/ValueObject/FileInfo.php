<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

class FileInfo
{
    private Checksum $checksum;
    private Filesize $filesize;

    public function __construct(Checksum $checksum, Filesize $filesize)
    {
        $this->checksum = $checksum;
        $this->filesize = $filesize;
    }

    public function getChecksum(): Checksum
    {
        return $this->checksum;
    }

    public function getFilesize(): Filesize
    {
        return $this->filesize;
    }
}
