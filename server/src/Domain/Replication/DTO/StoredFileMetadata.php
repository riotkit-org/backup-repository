<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO;

class StoredFileMetadata implements \JsonSerializable
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $rawMetadata;

    public function __construct(string $className, array $rawMetadata)
    {
        $this->className = $className;
        $this->rawMetadata = $rawMetadata;
    }

    public function jsonSerialize()
    {
        return $this->rawMetadata;
    }
}
