<?php declare(strict_types=1);

namespace App\Domain\Backup\Form;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;

class CollectionAddDeleteUserForm
{
    /**
     * @var ?BackupCollection
     */
    public ?BackupCollection $collection = null;

    /**
     * @var ?User
     */
    public ?User $user = null;
}
