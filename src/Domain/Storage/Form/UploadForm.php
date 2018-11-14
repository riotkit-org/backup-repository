<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

class UploadForm
{
    /**
     * @var string[]
     */
    public $tags;

    /**
     * @var string
     */
    public $password;

    /**
     * @var bool
     */
    public $fileOverwrite = false;

    /**
     * @var string
     */
    public $backUrl = '';
}
