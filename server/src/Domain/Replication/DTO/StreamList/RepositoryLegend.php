<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO\StreamList;

/**
 * Describes the format of an output CSV dump
 */
class RepositoryLegend implements \JsonSerializable
{
    private string $metadataUrlTemplate;
    private string $fetchUrlTemplate;
    private int $remainingSince;

    public function __construct(string $metadataUrlTemplate, string $fetchUrlTemplate, int $remainingSince)
    {
        $this->metadataUrlTemplate = $metadataUrlTemplate;
        $this->fetchUrlTemplate    = $fetchUrlTemplate;
        $this->remainingSince      = $remainingSince;
    }

    public function jsonSerialize()
    {
        return [
            'metadataUrlTemplate' => $this->metadataUrlTemplate,
            'fetchUrlTemplate'    => $this->fetchUrlTemplate,
            'remainingSince'      => $this->remainingSince
        ];
    }
}
