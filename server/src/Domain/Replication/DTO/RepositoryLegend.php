<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO;

use App\Domain\Replication\Contract\CsvSerializable;

/**
 * File in the storage. Short version of only necessary metadata, as Replication domain expects huge amounts of data.
 */
class RepositoryLegend implements CsvSerializable
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

    public function toCSV(): string
    {
        return $this->metadataUrlTemplate . self::SEP . $this->fetchUrlTemplate;
    }
}
