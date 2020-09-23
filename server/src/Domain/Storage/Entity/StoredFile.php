<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

use App\Domain\Common\ValueObject\Password;
use App\Domain\Storage\Exception\InvalidAttributeException;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Common\SharedEntity\StoredFile as StoredFileFromCommon;
use App\Domain\Storage\ValueObject\Path;

/**
 * Represents a file that is (or will be) stored in the storage
 *
 * @method Filename getFilename()
 */
class StoredFile extends StoredFileFromCommon implements \JsonSerializable
{
    /**
     * @var \DateTimeImmutable
     */
    protected $dateAdded;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var bool
     */
    protected $public;

    /**
     * @var Tag[]
     */
    private $tags;

    /**
     * @var Attribute[]
     */
    private $attributes;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateAdded  = new \DateTimeImmutable();
        $this->timezone   = \date_default_timezone_get();
        $this->tags       = [];
        $this->attributes = [];
    }

    /**
     * @param Filename $filename
     * @param string $submittedBy
     *
     * @return StoredFile
     *
     * @throws \Exception
     */
    public static function newFromFilename(Filename $filename, string $submittedBy): StoredFile
    {
        $new = new static();
        $new->fileName    = $filename->getValue();
        $new->submittedBy = $submittedBy;
        $new->dateAdded   = new \DateTimeImmutable();
        $new->tags        = [];
        $new->attributes  = [];

        return $new;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        // in case of a ORM collection class
        if (\is_object($this->tags)) {
            $this->tags = $this->tags->toArray();
        }

        return $this->tags;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        if (\is_object($this->attributes)) {
            $this->attributes = $this->attributes->toArray();
        }

        return $this->attributes;
    }

    public function addTag(Tag $tag): void
    {
        $tags = $this->getTags();

        // do not allow to add duplicates
        if ($this->hasTagNamed($tag->getName())) {
            return;
        }

        $tags[] = $tag;
        $this->tags = \array_unique($tags);
    }

    public function addAttribute(Attribute $attribute): void
    {
        $attributes = $this->getAttributes();

        if ($this->hasAttributeNamed($attribute->getName())) {
            throw InvalidAttributeException::createDuplicatedAttributeException($attribute->getName());
        }

        $attributes[] = $attribute;
        $this->attributes = $attributes;
    }

    public function hasTagNamed(string $name): bool
    {
        $tag = new Tag();
        $tag->setName($name);

        foreach ($this->getTags() as $existingTag) {
            if ($existingTag->isSameContentAs($tag)) {
                return true;
            }
        }

        return false;
    }

    public function hasAttributeNamed(string $name): bool
    {
        $attribute = new Attribute($this, $name, '');

        foreach ($this->attributes as $existing) {
            if ($existing->hasSameNameAs($attribute)) {
                return true;
            }
        }

        return false;
    }

    public function isFileTaggedWithAnyOfThose(array $tags): bool
    {
        if (empty($tags) && empty($this->getTags())) {
            return true;
        }

        foreach ($tags as $tag) {
            if ($this->hasTagNamed($tag)) {
                return true;
            }
        }

        return false;
    }

    public function setDateAdded(\DateTimeImmutable $date): StoredFile
    {
        $this->dateAdded = $date;

        return $this;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function isPasswordProtected(): bool
    {
        return !empty($this->password);
    }

    public function replaceEncodedPassword(Password $password): void
    {
        $this->password = $password->getValue();
    }

    public function changePassword(?string $newPassword): void
    {
        if (!$newPassword) {
            $this->password = '';

            return;
        }

        $this->password = $this->encryptPassword($newPassword);
    }

    public function checkPasswordMatchesWith(string $password): bool
    {
        if (!$this->isPasswordProtected()) {
            return true;
        }

        return $this->encryptPassword($password) === $this->password;
    }

    private function encryptPassword(string $password): string
    {
        return hash('sha256', $password);
    }

    public function getDateAdded(): \DateTimeImmutable
    {
        return $this->dateAdded;
    }


    public function isPublic(): bool
    {
        return $this->public;
    }

    public function checkContentHashMatchesEtag(string $etag): bool
    {
        return $this->contentHash === $etag;
    }

    public function jsonSerialize(): array
    {
        return [
            'publicUrl'   => '',
            'filename'    => $this->getFilename(),
            'contentHash' => $this->getContentHash(),
            'dateAdded'   => $this->getDateAdded(),
            'timezone'    => $this->getTimezone(),
            'tags'        => $this->getTags(),
            'public'      => $this->public,
            'attributes'  => [
                'isPasswordProtected' => $this->isPasswordProtected()
            ]
        ];
    }

    public function jsonSerializeAdmin(): array
    {
        return [
            'attributes' => [
                'path' => $this->getStoragePath()->getValue()
            ]
        ];
    }

    protected static function getFilenameClass(): string
    {
        return Filename::class;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone($this->timezone);
    }

    public function setToPointAtExistingPathInStorage(StoredFile $getAlreadyExistingFile): void
    {
        $this->storagePath = $getAlreadyExistingFile->getStoragePath()->getValue();
    }

    public function fillUpStoragePathIfEmpty(): void
    {
        if ($this->storagePath) {
            return;
        }

        $this->storagePath = $this->getFilename()->getValue();
    }

    public function getStoragePath(): Path
    {
        return Path::fromCompletePath($this->storagePath);
    }

    public function isUniqueInStorage(): ?bool
    {
        return $this->getFilename()->getValue() !== $this->getStoragePath()->getValue();
    }

    public function wasSubmittedByTokenId(?string $id): bool
    {
        return $id === $this->submittedBy;
    }
}
