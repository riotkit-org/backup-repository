<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory;

use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Repository\TagRepository;
use App\Domain\Storage\ValueObject\Filename;

class StoredFileFactory
{
    /**
     * @var TagRepository
     */
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function createFromForm(UploadForm $form, Filename $filename): StoredFile
    {
        $storedFile = StoredFile::newFromFilename($filename);
        $this->mapFromForm($form, $storedFile);

        return $storedFile;
    }

    /**
     * @param UploadForm $form
     * @param StoredFile $storedFile
     *
     * @return StoredFile
     */
    public function mapFromForm(UploadForm $form, StoredFile $storedFile): StoredFile
    {
        $tags = $this->tagRepository->findOrCreateTagsByNames($form->tags);

        foreach ($tags as $tag) {
            $storedFile->addTag($tag);
        }

        return $storedFile;
    }
}
