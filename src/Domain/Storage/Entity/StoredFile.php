<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

use App\Domain\Storage\ValueObject\Checksum;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Mime;
use App\Domain\Common\SharedEntity\StoredFile as StoredFileFromCommon;

/**
 * Represents a file that is (or will be) stored in the storage
 */
class StoredFile extends StoredFileFromCommon implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $contentHash = '';

    /**
     * @var \DateTimeImmutable
     */
    protected $dateAdded;

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var Tag[]
     */
    private $tags;

    public function __construct()
    {
        $this->dateAdded = new \DateTimeImmutable();
        $this->tags      = [];
    }

    public static function newFromFilename(Filename $filename): StoredFile
    {
        $new = new static();
        $new->fileName = $filename->getValue();
        $new->dateAdded = new \DateTimeImmutable();
        $new->tags = [];

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

    public function wasAlreadyStored(): bool
    {
        return $this->contentHash !== '';
    }

    /**
     * @param Checksum $contentHash
     *
     * @return StoredFile
     */
    public function setContentHash(Checksum $contentHash): StoredFile
    {
        $this->contentHash = $contentHash->getValue();
        return $this;
    }

    /**
     * @param Mime $mimeType
     *
     * @return StoredFile
     */
    public function setMimeType(Mime $mimeType): StoredFile
    {
        $this->mimeType = $mimeType->getValue();

        return $this;
    }

    public function setDateAdded(\DateTimeImmutable $date): StoredFile
    {
        $this->dateAdded = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function isPasswordProtected(): bool
    {
        return !empty($this->password);
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

    public function checkContentHashMatchesEtag(string $etag): bool
    {
        return $this->contentHash === $etag;
    }

    private function encryptPassword(string $password): string
    {
        return hash('sha256', $password);
    }

    public function getDateAdded(): \DateTimeImmutable
    {
        return $this->dateAdded;
    }

    public function getContentHash(): string
    {
        return $this->contentHash;
    }

    public function jsonSerialize()
    {
        return [
            'publicUrl'   => '',
            'filename'    => $this->getFilename(),
            'contentHash' => $this->getContentHash(),
            'dateAdded'   => $this->getDateAdded(),
            'mimeType'    => $this->getMimeType(),
            'tags'        => $this->getTags(),
            'attributes'  => [
                'isPasswordProtected' => $this->isPasswordProtected()
            ]
        ];
    }

    protected static function getFilenameClass(): string
    {
        return Filename::class;
    }
}
