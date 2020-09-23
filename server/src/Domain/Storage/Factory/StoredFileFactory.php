<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Common\ValueObject\Password;
use App\Domain\Storage\Entity\Attribute;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\InvalidAttributeException;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Repository\TagRepository;
use App\Domain\Storage\ValueObject\Filename;

/**
 * Factory - Produces complete StoredFile objects basing on UploadForm
 *           The resulting objects are ready to persist by the persistence layer.
 */
class StoredFileFactory
{
    private TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param UploadForm $form
     * @param Filename $filename
     * @param Token $token
     *
     * @return StoredFile
     *
     * @throws InvalidAttributeException
     */
    public function createFromForm(UploadForm $form, Filename $filename, Token $token): StoredFile
    {
        $storedFile = StoredFile::newFromFilename($filename, $token->getId());
        $this->mapFromForm($form, $storedFile);

        return $storedFile;
    }

    /**
     * @param UploadForm $form
     * @param StoredFile $storedFile
     *
     * @return StoredFile
     *
     * @throws InvalidAttributeException
     */
    public function mapFromForm(UploadForm $form, StoredFile $storedFile): StoredFile
    {
        // fields
        $form->password instanceof Password
            ? $storedFile->replaceEncodedPassword($form->password)
            : $storedFile->changePassword($form->password);

        $storedFile->setPublic($form->public);

        // related tags
        $tags = $this->tagRepository->findOrCreateTagsByNames($form->tags);

        foreach ($tags as $tag) {
            $storedFile->addTag($tag);
        }

        // attributes
        $attributes = $this->createAttributesFromJson($storedFile, $form->getAttributes());

        foreach ($attributes as $attribute) {
            $storedFile->addAttribute($attribute);
        }

        return $storedFile;
    }

    private function createAttributesFromJson(StoredFile $storedFile, string $json): array
    {
        $asArray    = \json_decode($json, true);
        $attributes = [];

        if (!is_array($asArray)) {
            throw new \InvalidArgumentException('Attributes are not a json array');
        }

        foreach ($asArray as $name => $value) {
            if (!is_int($value) && !is_string($value) && !is_bool($value)) {
                throw new \InvalidArgumentException('"' . $name . '" has value of invalid type, accepted only: integer, string, boolean');
            }

            $attributes[] = new Attribute($storedFile, $name, $value);
        }

        return $attributes;
    }
}
