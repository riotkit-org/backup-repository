<?php declare(strict_types=1);

namespace App\Domain\Authentication\Service;

use App\Domain\Authentication\Entity\User;
use App\Domain\PermissionsReference;

class PermissionsFilter
{
    public const FILTER_COLLECTION = 'collection';
    public const FILTER_AUTH       = 'auth';

    public static function filterBy(array $roles, array $filterNames, User $user): array
    {
        $availableFilters = [
            static::FILTER_AUTH =>
                fn($currentRoles) => static::filterByAuth($currentRoles, $user),

            static::FILTER_COLLECTION =>
                fn($currentRoles) => static::filterByAllowedPerCollectionOnly($currentRoles, $user),
        ];

        foreach ($availableFilters as $filterName => $filteringMethod) {
            if (in_array($filterName, $filterNames, true)) {
                $roles = $filteringMethod($roles, $user);
            }
        }

        return $roles;
    }

    /**
     * Leaves only permissions that are allowed to be assigned per Backup Collection
     *
     * @param array $permissions
     * @param User $user
     *
     * @return array
     */
    protected static function filterByAllowedPerCollectionOnly(array $permissions, User $user): array
    {
        return array_filter(
            $permissions,
            function (string $role) {
                return in_array($role, PermissionsReference::PER_BACKUP_COLLECTION_LIST);
            },
            static::isFlatListWithoutDescriptions($permissions) ? 0 : ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Filter by user token permissions, so the user can see it's permissions
     *
     * @param array $permissions
     * @param User  $user
     *
     * @return array
     */
    protected static function filterByAuth(array $permissions, User $user): array
    {
        $isAdministrator = $user->isAdministrator();

        return array_filter(
            $permissions,
            function (string $role) use ($user, $isAdministrator) {

                // administrator is also allowed to grant restrictions
                if ($isAdministrator && in_array($role, PermissionsReference::getRestrictionsList())) {
                    return true;
                }

                return $user->hasRole($role);
            },
            static::isFlatListWithoutDescriptions($permissions) ? 0 : ARRAY_FILTER_USE_KEY
        );
    }

    private static function isFlatListWithoutDescriptions(array $permissions): bool
    {
        // does not matter at all
        if (!$permissions) {
            return true;
        }

        return is_numeric(array_keys($permissions)[0]);
    }
}
