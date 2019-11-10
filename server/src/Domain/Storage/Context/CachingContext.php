<?php declare(strict_types=1);

namespace App\Domain\Storage\Context;

use App\Domain\Storage\Entity\StoredFile;

class CachingContext
{
    /**
     * @var string
     */
    private $etag;

    /**
     * @var \DateTimeImmutable
     */
    private $timestamp;

    public function __construct(string $etag, \DateTimeImmutable $timestamp = null)
    {
        $this->etag      = $etag;
        $this->timestamp = $timestamp;
    }

    public function isCacheExpiredForFile(StoredFile $file): bool
    {
        if ($this->timestamp && $file->getDateAdded() > $this->timestamp) {
            return true;
        }

        return !$file->checkContentHashMatchesEtag($this->etag);
    }
}
