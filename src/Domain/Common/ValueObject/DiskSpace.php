<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\ValueObject\Numeric\PositiveNumberOrZero;

class DiskSpace extends PositiveNumberOrZero implements \JsonSerializable
{
    public function __construct($size)
    {
        $exceptionType = static::getExceptionType();

        if (!\is_string($size) && !\is_int($size)) {
            throw new $exceptionType('cannot_parse_disk_space_check_format');
        }

        try {
            $valueInBytes = (int) \ByteUnits\parse($size)->numberOfBytes();

            parent::__construct($valueInBytes);

        } catch (\Exception $exception) {
            throw new $exceptionType('cannot_parse_disk_space_check_format');
        }
    }

    public static function fromBytes(int $bytes)
    {
        return new static($bytes . 'b');
    }

    public function toHumanReadable(): string
    {
        return \ByteUnits\Metric::bytes($this->value)->format();
    }

    /**
     * @param DiskSpace $additionalElement
     *
     * @return static
     */
    public function add(DiskSpace $additionalElement)
    {
        $new = clone $this;
        $new->value += $additionalElement->getValue();

        return $new;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }
}
