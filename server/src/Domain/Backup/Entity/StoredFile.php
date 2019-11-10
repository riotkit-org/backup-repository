<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Common\SharedEntity\StoredFile as StoredFileFromCommon;

/**
 * @method Filename getFilename()
 */
class StoredFile extends StoredFileFromCommon implements \JsonSerializable
{
    protected static function getFilenameClass(): string
    {
        return Filename::class;
    }

    public function jsonSerialize()
    {
        return [
            'id'       => $this->getId(),
            'filename' => $this->getFilename()
        ];
    }
}
