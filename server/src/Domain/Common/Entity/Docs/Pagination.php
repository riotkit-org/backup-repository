<?php declare(strict_types=1);

namespace App\Domain\Common\Entity\Docs;

abstract class Pagination
{
    /**
     * @var int
     */
    public $current;

    /**
     * @var int
     */
    public $max;

    /**
     * @var int
     */
    public $perPage;
}
