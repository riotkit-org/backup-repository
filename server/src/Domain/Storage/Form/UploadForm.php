<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

use App\Domain\Common\Form\ApplicationForm;
use App\Domain\Common\ValueObject\Password;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Entity\Tag;

class UploadForm extends ApplicationForm
{
    /**
     * @internal ApplicationForm::typeInSchema array
     * @var string[]
     */
    public $tags;

    /**
     * @internal ApplicationForm::typeInSchema string
     * @var string|Password
     */
    public $password;

    /**
     * @internal ApplicationForm::typeInSchema bool
     * @var bool
     */
    public $fileOverwrite = false;

    /**
     * @internal ApplicationForm::typeInSchema string
     * @var string
     */
    public $backUrl = '';

    /**
     * @internal ApplicationForm::typeInSchema bool
     * @var bool
     */
    public $public = true;

    /**
     * @internal ApplicationForm::typeInSchema string
     * @var string
     */
    public $contentIdent = '';

    /**
     * eg. base64 (if the data in body is encoded with base64 and needs to be decoded)
     *
     * @internal ApplicationForm::typeInSchema string
     * @var string|null
     */
    public $encoding;

    /**
     * Optional stream to use instead of HTTP, when using internally with DomainBus
     *
     * @var resource|null
     */
    public $stream;

    public static function createFromFile(StoredFile $file): UploadForm
    {
        $form = new static();

        // defaults
        $form->contentIdent  = '';
        $form->backUrl       = '';
        $form->fileOverwrite = false;

        // mapped
        $form->tags          = \array_map(function (Tag $tag) { return $tag->getName(); }, $file->getTags());
        $form->password      = $file->getPassword();
        $form->public        = $file->isPublic();

        return $form;
    }

    public function toArray(): array
    {
        return [
            'contentIdent'  => $this->contentIdent,
            'backUrl'       => $this->backUrl,
            'fileOverwrite' => $this->fileOverwrite,
            'tags'          => $this->tags,
            'password'      => $this->password,
            'public'        => $this->public
        ];
    }
}
