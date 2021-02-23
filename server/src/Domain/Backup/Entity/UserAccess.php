<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\ValueObject\CollectionSpecificPermissions;

/**
 * This entity is a connection bridge between Collection <-> User
 * Normally it is fully managed transparently by ORM, but in this case we manage it semi-manually to inject
 * roles per connection.
 */
class UserAccess implements \JsonSerializable
{
    private string $collectionId;
    private string $userId;
    private Authentication\User $user;
    private CollectionSpecificPermissions $roles;

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

    public function setRoles(CollectionSpecificPermissions $roles)
    {
        $this->roles = $roles;
    }

    public function getRoles(): CollectionSpecificPermissions
    {
        return $this->roles;
    }

    public function jsonSerialize(): array
    {
        return [
            'userId'       => $this->userId,
            'userEmail'    => $this->user->getEmail()->getValue(),
            'roles'        => $this->roles->getAsList(),
            'collectionId' => $this->collectionId
        ];
    }
}
