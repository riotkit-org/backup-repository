<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class DiskSpace extends BaseValueObject implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $value;

    public function __construct(string $size)
    {
        try {
            $this->value = (int) \ByteUnits\parse($size)->numberOfBytes();

        } catch (\Exception $exception) {
            $exceptionType = static::getExceptionType();
            throw new $exceptionType('Cannot parse file size into bytes, make sure the format is correct');
        }
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
