<?php declare(strict_types=1);

namespace App\Domain\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\UserAccess;

interface UserAccessRepository
{
    public function findForCollectionAndUser(BackupCollection $collection, User $user): ?UserAccess;

    public function persist(UserAccess $userAccess): void;
}
