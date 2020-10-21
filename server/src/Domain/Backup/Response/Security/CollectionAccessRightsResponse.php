<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Security;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;

class CollectionAccessRightsResponse implements \JsonSerializable
{
    private string $status = '';
    private int $exitCode = 0;
    private array $data = [];

    public static function createFromResults(User $user, BackupCollection $collection): CollectionAccessRightsResponse
    {
        $new = new static();
        $new->status    = 'OK';
        $new->exitCode  = 200;
        $new->data      = [
            'user'         => $user,
            'collection'   => $collection,
            'users_count'  => \count($collection->getAllowedUsers())
        ];

        return $new;
    }

    public static function createWithNotFoundError(): CollectionAccessRightsResponse
    {
        $new = new static();
        $new->status    = 'Object not found';
        $new->exitCode  = 404;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'http_code'  => $this->exitCode,
            'data'       => $this->data
        ];
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->exitCode;
    }
}
