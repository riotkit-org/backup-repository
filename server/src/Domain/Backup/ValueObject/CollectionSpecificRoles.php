<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject;

use App\Domain\Common\ValueObject\Roles;

class CollectionSpecificRoles extends Roles
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
        return \App\Domain\Roles::PER_BACKUP_COLLECTION_LIST;
    }
}
