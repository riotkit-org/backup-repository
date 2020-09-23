<?php declare(strict_types=1);

namespace App\Domain\Backup\Form;

use App\Domain\Backup\Entity\BackupCollection;

class BackupSubmitForm
{
    /**
     * @var BackupCollection
     */
    public $collection;

    /**
     * Serialized JSON with file attributes (key-value store)
     *
     * @var string
     */
    public $attributes;
}
