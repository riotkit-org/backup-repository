<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\ValueObject\CollectionSpecificPermissions;

/**
 * This entity is a connection bridge between Collection <-> User
 * Normally it is fully managed transparently by ORM, but in this case we manage it semi-manually to inject
 * permissions per connection.
 */
class UserAccess implements \JsonSerializable
{
    private string $collectionId;
    private string $userId;
    private Authentication\User $user;
    private CollectionSpecificPermissions $permissions;

    public static function createFrom(BackupCollection $collection, Authentication\User $user)
    {
        if (!$collection->getId() || !$user->getId()) {
            throw new \LogicException('Collection or User has no id');
        }

        $ua = new static();
        $ua->collectionId = (string) $collection->getId();
        $ua->userId       = (string) $user->getId();
        $ua->user         = $user;

        return $ua;
    }

    public function setPermissions(CollectionSpecificPermissions $permissions)
    {
        $this->permissions = $permissions;
    }

    public function getPermissions(): CollectionSpecificPermissions
    {
        return $this->permissions;
    }

    public function jsonSerialize(): array
    {
        return [
            'user_id'       => $this->userId,
            'user_email'    => $this->user->getEmail()->getValue(),
            'permissions'   => $this->permissions->getAsList(),
            'collection_id' => $this->collectionId
        ];
    }
}
