<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory;

use App\Domain\Storage\Form\UploadByPostForm;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Url;

class FileNameFactory
{
    public function fromUrl(Url $url): Filename
    {
        $parts = explode('?', $url->getValue());

        return new Filename(
            $url->getReproducibleHash() .
            '_' .
            pathinfo($parts[0], PATHINFO_BASENAME)
        );
    }

    public function fromForm(UploadByPostForm $form): Filename
    {
        return new Filename(
            $this->generateRandomName() . basename($form->fileName)
        );
    }

    private function generateRandomName(): string
    {
        return \bin2hex(\openssl_random_pseudo_bytes(5));
    }
}
