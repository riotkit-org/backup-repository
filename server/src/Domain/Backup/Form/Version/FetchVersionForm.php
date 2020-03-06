<?php declare(strict_types=1);

namespace App\Domain\Backup\Form\Version;

use App\Domain\Backup\Entity\BackupCollection;

class FetchVersionForm
{
    /**
     * @var BackupCollection
     */
    public $collection;

    /**
     * @var string
     */
    public $versionId;

    /**
     * @var bool
     */
    public $redirect;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $httpBytesRange;

    /**
     * @var string
     */
    public $httpIfNoneMatch;

    /**
     * @var string
     */
    public $httpIfModifiedSince;
}
