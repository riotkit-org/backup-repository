<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\ValueObject\Filename;

class UploadByPostForm extends UploadForm
{
    /**
     * @var Filename
     */
    public $fileName;

    /**
     * @var bool|string
     */
    public $stripInvalidCharacters;

    /**
     * Is this file name a final, and should not be changed?
     * Used at least in securecopy.
     */
    public bool $isFinalFilename = false;

    public static function createFromFile(StoredFile $file): UploadByPostForm
    {
        /**
         * @var $form UploadByPostForm
         */
        $form = parent::createFromFile($file);
        $form->fileName               = $file->getFilename();
        $form->stripInvalidCharacters = false;

        return $form;
    }

    public function toArray(): array
    {
        $asArray = parent::toArray();
        $asArray['fileName']               = $this->fileName;
        $asArray['stripInvalidCharacters'] = $this->stripInvalidCharacters;

        return $asArray;
    }
}
