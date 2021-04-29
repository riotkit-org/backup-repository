<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonStorageException;
use App\Domain\Common\ValueObject\Numeric\PositiveNumberOrZero;

use ByteUnits\Metric;
use function ByteUnits\parse;

class DiskSpace extends PositiveNumberOrZero implements \JsonSerializable
{
    /**
     * @param string|int $size
     *
     * @throws CommonStorageException
     */
    public function __construct($size)
    {
        if (!\is_string($size) && !\is_int($size)) {
            throw CommonStorageException::fromDiskSpaceFormatParsingErrorCause();
        }

        try {
            $valueInBytes = (int) parse($size)->numberOfBytes();

            parent::__construct($valueInBytes);

        } catch (\Exception $exception) {
            throw CommonStorageException::fromDiskSpaceFormatParsingErrorCause($exception);
        }
    }

    public static function fromBytes(int $bytes)
    {
        return new static($bytes . 'b');
    }

    public function toHumanReadable(): string
    {
        return Metric::bytes($this->value)->format();
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

    public function jsonSerialize(): int
    {
        return $this->getValue();
    }
}
