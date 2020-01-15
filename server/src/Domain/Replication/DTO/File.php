<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO;

/**
 * File in the storage. Short version of only necessary metadata, as Replication domain expects huge amounts of data.
 */
class File implements \JsonSerializable
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

    /**
     * Do not use objects: It's expected that File instances could be returned in milions of copies
     *
     * @var string
     */
    private $timezone;

    public function __construct(int $fileId, string $filename, string $timestamp, string $hash, string $timezone)
    {
        $this->fileId    = $fileId;
        $this->filename  = $filename;
        $this->timestamp = $timestamp;
        $this->hash      = $hash;
        $this->timezone  = $timezone;
    }

    public function jsonSerialize(): array
    {
        return [
            'filename'  => $this->filename,
            'fileId'    => $this->fileId,
            'timestamp' => $this->timestamp,
            'hash'      => $this->hash,
            'timezone'  => $this->timestamp
        ];
    }

    public static function fromArray(array $attributes): File
    {
        return new static(
            $attributes['fileId'],
            $attributes['filename'],
            $attributes['timestamp'],
            $attributes['hash'],
            $attributes['timezone']
        );
    }

    public function getTimestamp(): \DateTime
    {
        return new \DateTime($this->timestamp);
    }
}
