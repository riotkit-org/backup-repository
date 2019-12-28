<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO;

use App\Domain\Replication\Contract\CsvSerializable;

/**
 * File in the storage. Short version of only necessary metadata, as Replication domain expects huge amounts of data.
 */
class File implements CsvSerializable
{
    /**
     * Do not use objects: It's expected that File instances could be returned in milions of copies
     *
     * @var string
     */
    private $filename;

    /**
     * Do not use objects: It's expected that File instances could be returned in milions of copies
     *
     * @var string
     */
    private $timestamp;

    /**
     * Do not use objects: It's expected that File instances could be returned in milions of copies
     *
     * @var string
     */
    private $hash;

    public function __construct(string $filename, string $timestamp, string $hash)
    {
        $this->filename  = $filename;
        $this->timestamp = $timestamp;
        $this->hash      = $hash;
    }

    public function toCSV(): string
    {
        return $this->filename . CsvSerializable::SEP . $this->timestamp . CsvSerializable::SEP . $this->hash;
    }
}
