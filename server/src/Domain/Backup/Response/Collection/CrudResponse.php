<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Collection;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Common\Response\NormalResponse;

class CrudResponse extends NormalResponse implements \JsonSerializable
{
    private ?BackupCollection $collection;

    public static function createWithNotFoundError(): CrudResponse
    {
        $new = new static();
        $new->status   = false;
        $new->message  = 'Object not found';
        $new->httpCode = 404;

        return $new;
    }

    public static function createSuccessfulResponse(BackupCollection $collection, int $status = 201): CrudResponse
    {
        $new = new static();
        $new->status     = true;
        $new->message    = 'OK';
        $new->httpCode   = $status;
        $new->collection = $collection;

        return $new;
    }

    public static function deletionSuccessfulResponse(BackupCollection $collection): CrudResponse
    {
        $new = new static();
        $new->message     = 'OK, collection was deleted';
        $new->status     = true;
        $new->httpCode   = 200;
        $new->collection = $collection;

        return $new;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['collection'] = $this->collection;

        return $data;
    }

    public function isSuccess(): bool
    {
        return \strpos($this->status, 'OK') === 0;
    }

    public function getCollection(): ?BackupCollection
    {
        return $this->collection;
    }
}
