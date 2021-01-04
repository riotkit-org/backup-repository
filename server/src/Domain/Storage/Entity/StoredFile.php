<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

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
    protected \DateTimeImmutable $dateAdded;
    protected string $timezone;

    /**
     * @var Tag[]
     */
    private array $tags;

    private int $filesize;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateAdded  = new \DateTimeImmutable();
        $this->timezone   = \date_default_timezone_get();
        $this->tags       = [];
        $this->filesize   = 0;
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

    public function setDateAdded(\DateTimeImmutable $date): StoredFile
    {
        $this->dateAdded = $date;

        return $this;
    }

    public function getDateAdded(): \DateTimeImmutable
    {
        return $this->dateAdded;
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
            'filesize'    => $this->getFilesize()
        ];
    }

    protected static function getFilenameClass(): string
    {
        return Filename::class;
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

    public function setFilesize(int $filesize): void
    {
        $this->filesize = $filesize;
    }

    public function getFilesize(): int
    {
        return $this->filesize;
    }
}
