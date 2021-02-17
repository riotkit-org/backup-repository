<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Security;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Common\Response\NormalResponse;

class CollectionAccessRightsResponse extends NormalResponse implements \JsonSerializable
{
    private array $data = [];

    public static function createFromResults(User $user, BackupCollection $collection): CollectionAccessRightsResponse
    {
        $new = new static();
        $new->status    = true;
        $new->message   = 'OK';
        $new->httpCode  = 200;
        $new->data      = [
            'user'         => $user,
            'collection'   => $collection,
            'users_count'  => \count($collection->getAllowedUsers())
        ];

        return $new;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['data'] = $this->data;

        return $data;
    }
}
