<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\ValueObject\CollectionSpecificRoles;

/**
 * This entity is a connection bridge between Collection <-> User
 * Normally it is fully managed transparently by ORM, but in this case we manage it semi-manually to inject
 * roles per connection.
 */
class UserAccess
{
    private string $collectionId;
    private string $userId;

    private CollectionSpecificRoles $roles;

    public static function createFrom(BackupCollection $collection, Authentication\User $user)
    {
        if (!$collection->getId() || !$user->getId()) {
            throw new \LogicException('Collection or User has no id');
        }

        $ua = new static();
        $ua->collectionId = (string) $collection->getId();
        $ua->userId       = (string) $user->getId();

        return $ua;
    }

    public function setRoles(CollectionSpecificRoles $roles)
    {
        $this->roles = $roles;
    }

    public function getRoles(): CollectionSpecificRoles
    {
        return $this->roles;
    }
}
