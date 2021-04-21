<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Common\SharedEntity\StoredFile as StoredFileFromCommon;
use App\Domain\Common\ValueObject\DiskSpace;

/**
 * @method Filename getFilename()
 */
class StoredFile extends StoredFileFromCommon implements \JsonSerializable
{
    private int $filesize;

    protected static function getFilenameClass(): string
    {
        return Filename::class;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'                      => $this->getId(),
            'filename'                => $this->getFilename(),
            'filesize_bytes'          => $this->filesize,
            'filesize'                => DiskSpace::fromBytes($this->filesize)->toHumanReadable()
        ];
    }
}
