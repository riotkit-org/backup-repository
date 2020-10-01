<?php declare(strict_types=1);

namespace App\Domain\Backup\Form;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;

class CollectionAddDeleteTokenForm
{
    /**
     * @var BackupCollection
     */
    public $collection;

    /**
     * @var User
     */
    public $token;
}
