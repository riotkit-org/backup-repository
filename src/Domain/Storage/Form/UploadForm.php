<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

use App\Domain\Common\ValueObject\Password;

class UploadForm
{
    /**
     * @var string[]
     */
    public $tags;

    /**
     * @var string|Password
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

    /**
     * @var bool
     */
    public $public = true;
}
