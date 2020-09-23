<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory;

use App\Domain\Storage\Form\UploadByPostForm;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Url;

class FileNameFactory
{
    public function fromForm(UploadByPostForm $form): Filename
    {
        if ($form->isFinalFilename) {
            return new Filename($form->fileName);
        }

        return new Filename(
            $this->generateRandomName() . basename($form->fileName),
            $form->stripInvalidCharacters
        );
    }

    private function generateRandomName(): string
    {
        return \bin2hex(\random_bytes(5));
    }
}
