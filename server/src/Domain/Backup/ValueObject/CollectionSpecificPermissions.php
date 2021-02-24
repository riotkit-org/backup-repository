<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject;

use App\Domain\Common\ValueObject\Permissions;

class CollectionSpecificPermissions extends Permissions
{
    /**
     * @return static
     *
     * @throws \App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError
     */
    public static function fromAllRolesGranted()
    {
        return static::fromArray(static::getAvailablePermissions());
    }

    protected static function getAvailablePermissions(): array
    {
        return \App\Domain\PermissionsReference::PER_BACKUP_COLLECTION_LIST;
    }
}
