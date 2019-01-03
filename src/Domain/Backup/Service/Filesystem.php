<?php declare(strict_types=1);

namespace App\Domain\Backup\Service;

use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Backup\ValueObject\FileSize;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\DomainBus;

class Filesystem
{
    /**
     * @var DomainBus
     */
    private $bus;

    public function __construct(DomainBus $bus)
    {
        $this->bus = $bus;
    }

    public function getFileSize(Filename $filename): FileSize
    {
        /**
         * @var \App\Domain\Common\ValueObject\DiskSpace $size
         */
        $size = $this->bus->call(Bus::STORAGE_GET_FILE_SIZE, [$filename]);

        return FileSize::fromBytes($size->getValue());
    }
}
