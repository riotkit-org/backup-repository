<?php declare(strict_types=1);

namespace App\Domain\Backup\Form\Collection;

class CreationForm
{
    /**
     * @var string|null
     */
    public $id;

    /**
     * @var int
     */
    public $maxBackupsCount;

    /**
     * @var int
     */
    public $maxOneVersionSize;

    /**
     * @var int
     */
    public $maxCollectionSize;

    /**
     * @var string
     */
    public $strategy;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $filename;
}
