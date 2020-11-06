<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Version;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Common\Response\NormalResponse;

class BackupSubmitResponse extends NormalResponse implements \JsonSerializable
{
    /**
     * @var StoredVersion
     */
    private StoredVersion $version;

    /**
     * @var BackupCollection
     */
    private BackupCollection $collection;

    public static function createSuccessResponse(StoredVersion $version, BackupCollection $collection): self
    {
        $new = new static();
        $new->message    = 'File was uploaded';
        $new->status     = true;
        $new->httpCode   = 200;
        $new->version    = $version;
        $new->collection = $collection;

        return $new;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data['version'] = $this->version;
        $data['collection'] = $this->collection;

        return $data;
    }
}
