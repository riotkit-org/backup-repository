<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

use App\Domain\Common\ValueObject\Password;
use App\Domain\Storage\ValueObject\InputEncoding;

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

    /**
     * @var string
     */
    public $contentIdent = '';

    /**
     * eg. base64 (if the data in body is encoded with base64 and needs to be decoded)
     *
     * @var string|null
     */
    public $encoding;
}
