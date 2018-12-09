<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\ValueObject\Numeric\PositiveNumberOrZero;

class DiskSpace extends PositiveNumberOrZero implements \JsonSerializable
{
    public function __construct(string $size)
    {
        try {
            $valueInBytes = (int) \ByteUnits\parse($size)->numberOfBytes();
            parent::__construct($valueInBytes);

        } catch (\Exception $exception) {
            $exceptionType = static::getExceptionType();
            throw new $exceptionType('cannot_parse_disk_space_check_format');
        }
    }

    public static function fromBytes(int $bytes)
    {
        return new static($bytes . 'b');
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function toHumanReadable(): string
    {
        return \ByteUnits\Metric::bytes($this->value)->format();
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }
}
