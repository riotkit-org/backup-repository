<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class DiskSpace
{
    /**
     * @var int
     */
    private $value;

    public function __construct(string $size)
    {
        $this->value = (int) \ByteUnits\parse($size)->numberOfBytes();
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function toHumanReadable(): string
    {
        return \ByteUnits\Metric::bytes($this->value)->format();
    }
}
