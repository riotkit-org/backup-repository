<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity\Docs;

use Swagger\Annotations as SWG;

abstract class CollectionDoc
{
    /**
     * @SWG\Property(type="string", example="d5aab8d7-64f3-42ce-bae4-59d7108294c3")
     *
     * @var string
     */
    public $id;

    /**
     * @SWG\Property(type="int", example="3")
     *
     * @var int
     */
    public $maxBackupsCount;

    /**
     * @SWG\Property(type="int", example="3221225472")
     *
     * @var int
     */
    public $maxOneBackupVersionSize;

    /**
     * @SWG\Property(type="int", example="9663676416")
     *
     * @var int
     */
    public $maxCollectionSize;

    /**
     * @SWG\Property(type="int", example="2022-05-01 08:00:00")
     *
     * @var \DateTime
     */
    public $createdAt;

    /**
     * @SWG\Property(type="string", example="delete_oldest_when_adding_new")
     *
     * @var string
     */
    public $strategy;

    /**
     * @SWG\Property(type="string", example="Database backup for International Workers Association")
     *
     * @var string
     */
    public $description;

    /**
     * @SWG\Property(type="string", example="iwa-ait-db.tar.gz")
     *
     * @var string
     */
    public $filename;
}
