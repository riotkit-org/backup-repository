<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class Checksum
{
    private const TYPES = [
        'sha256sum' => 64
    ];

    protected string $value;

    public function __construct(string $value, string $type)
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Unsupported checksum type');
        }

        if (\strlen($value) !== self::TYPES[$type]) {
            throw new \InvalidArgumentException('The checksum length does not match');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
