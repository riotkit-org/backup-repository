<?php declare(strict_types=1);

namespace App\Domain\Backup\Form\Version;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;

class VersionDeleteForm
{
    /**
     * @var BackupCollection
     */
    public $collection;

    /**
     * @var StoredVersion
     */
    public $version;
}
