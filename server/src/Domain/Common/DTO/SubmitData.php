<?php declare(strict_types=1);

namespace App\Domain\Common\DTO;

use App\Domain\SubmitDataTypes;
use DateTimeImmutable;
use DateTimeZone;

class SubmitData implements \JsonSerializable
{
    private string $type;
    private string $id;
    private DateTimeImmutable $date;
    private DateTimeZone $timezone;
    private array $rawMetadata;

    public function __construct(string $type, string $id, DateTimeImmutable $date, DateTimeZone $timezone, array $rawMetadata)
    {
        if (!\in_array($type, SubmitDataTypes::TYPES, true)) {
            throw new \InvalidArgumentException('Unrecognized SubmitData type "' . $type . '"');
        }

        $this->type        = $type;
        $this->id          = $id;
        $this->date        = $date;
        $this->timezone    = $timezone;
        $this->rawMetadata = $rawMetadata;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'id'   => $this->id,
            'date' => $this->date->getTimestamp(),
            'tz'   => $this->timezone->getName(),
            'form' => $this->rawMetadata
        ];
    }
}
