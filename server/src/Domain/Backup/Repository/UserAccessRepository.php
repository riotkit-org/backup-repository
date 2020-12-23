<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\UserAccess;

interface UserAccessRepository
{
    /**
     * Finds a single User Access details for given Backup Collection
     *
     * @param BackupCollection $collection
     * @param User $user
     * @return UserAccess|null
     */
    public function findForCollectionAndUser(BackupCollection $collection, User $user): ?UserAccess;

    /**
     * Finds all granted accesses to given Backup Collection
     *
     * @param BackupCollection $collection
     *
     * @return UserAccess[]
     */
    public function findAllAccessesForCollection(BackupCollection $collection): array;

    public function persist(UserAccess $userAccess): void;

    public function remove(UserAccess $userAccess): void;
}
