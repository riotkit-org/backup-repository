<?php declare(strict_types=1);

namespace App\Domain\Replication\Collection;

use App\Domain\Replication\Contract\MultiDocumentJsonSerializable;
use App\Domain\Replication\DTO\File;
use App\Domain\Replication\DTO\StreamList\SubmitData;
use JsonSerializable;

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
}
