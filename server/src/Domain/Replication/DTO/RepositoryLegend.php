<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO;

/**
 * Describes the format of an output CSV dump
 */
class RepositoryLegend implements \JsonSerializable
{
    /**
     * @var string
     */
    private $metadataUrlTemplate;

    /**
     * @var string
     */
    private $fetchUrlTemplate;

    public function __construct(string $metadataUrlTemplate, string $fetchUrlTemplate)
    {
        $this->metadataUrlTemplate = $metadataUrlTemplate;
        $this->fetchUrlTemplate    = $fetchUrlTemplate;
    }

    public function jsonSerialize()
    {
        return [
            'metadataUrlTemplate' => $this->metadataUrlTemplate,
            'fetchUrlTemplate'    => $this->fetchUrlTemplate
        ];
    }
}
