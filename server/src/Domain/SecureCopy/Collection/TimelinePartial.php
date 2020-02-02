<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Collection;

use App\Domain\SecureCopy\Contract\MultiDocumentJsonSerializable;
use App\Domain\SecureCopy\DTO\StreamList\SubmitData;

class TimelinePartial implements MultiDocumentJsonSerializable
{
    /**
     * @var SubmitData[]
     */
    private array $entries;
    private int $totalCount;

    public function __construct(array $entries, int $totalCount)
    {
        $this->entries    = $entries;
        $this->totalCount = $totalCount;
    }

    public function count(): int
    {
        return $this->totalCount;
    }

    public function toMultipleJsonDocuments(): string
    {
        $docs = '';

        foreach ($this->entries as $serializedEntry) {
            $docs .= \json_encode($serializedEntry, JSON_THROW_ON_ERROR, 512) . "\n";
        }

        return $docs;
    }

    public function withMerged(TimelinePartial $partial): TimelinePartial
    {
        $merged = new TimelinePartial($this->entries, $this->totalCount);
        $merged->entries = \array_merge($merged->entries, $partial->entries);
        $merged->totalCount += $partial->totalCount;

        return $merged;
    }
}
