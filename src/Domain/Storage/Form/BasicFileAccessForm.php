<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

abstract class BasicFileAccessForm
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $password;
}
