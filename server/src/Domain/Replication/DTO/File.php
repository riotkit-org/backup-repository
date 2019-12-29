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
     * @var int
     */
    private $fileId;

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

    public function __construct(int $fileId, string $filename, string $timestamp, string $hash)
    {
        $this->fileId    = $fileId;
        $this->filename  = $filename;
        $this->timestamp = $timestamp;
        $this->hash      = $hash;
    }

    public function toCSV(): string
    {
        return 'File' .
            self::SEP . $this->filename .
            self::SEP . $this->fileId .
            self::SEP . $this->timestamp .
            self::SEP . $this->hash;
    }
}
