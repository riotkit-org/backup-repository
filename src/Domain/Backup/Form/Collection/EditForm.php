<?php declare(strict_types=1);

namespace App\Domain\Backup\Form\Collection;

use App\Domain\Backup\Entity\BackupCollection;

class EditForm extends CreationForm
{
    /**
     * @var BackupCollection
     */
    public $collection;
}
