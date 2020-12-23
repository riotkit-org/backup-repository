<?php declare(strict_types=1);

namespace App\Domain\Authentication\Service;

use App\Domain\Authentication\Entity\User;
use App\Domain\Roles;

class RolesFilter
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
     * @param array $roles
     * @param User $user
     *
     * @return array
     */
    protected static function filterByAllowedPerCollectionOnly(array $roles, User $user): array
    {
        return array_filter($roles, function (string $role) {
            return in_array($role, Roles::PER_BACKUP_COLLECTION_LIST);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Filter by user token roles, so the user can see it's permissions
     *
     * @param array $roles
     * @param User  $user
     *
     * @return array
     */
    protected static function filterByAuth(array $roles, User $user): array
    {
        return array_filter($roles, function (string $role) use ($user) {
            return $user->hasRole($role);
        }, ARRAY_FILTER_USE_KEY);
    }
}
