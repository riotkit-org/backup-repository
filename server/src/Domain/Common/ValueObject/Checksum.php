<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonValueException;

class Checksum
{
    private const TYPES = [
        'sha256sum' => 64
    ];

    protected string $value;

    public function __construct(string $value, string $type)
    {
        if (!isset(self::TYPES[$type])) {
            throw CommonValueException::fromInvalidChecksumTypeCause();
        }

        if (\strlen($value) !== self::TYPES[$type]) {
            throw CommonValueException::fromChecksumLengthNotMatchingCause();
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
