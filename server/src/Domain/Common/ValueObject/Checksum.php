<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class Checksum
{
    private const TYPES = [
        'sha256sum' => 64
    ];

    private const TYPE_TO_ALGO_NAME = [
        'sha256sum' => 'sha256'
    ];

    protected string $value;

    public function __construct(string $value, string $type, string $salt = '')
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Unsupported checksum type');
        }

        if ($salt) {
            $value = $this->rehashWithSalt($value, $type, $salt);
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

    private function rehashWithSalt(string $value, string $type, string $salt): string
    {
        if (!isset(self::TYPE_TO_ALGO_NAME[$type])) {
            throw new \LogicException('TYPE_TO_ALGO_NAME mapping not implemented for type "' . $type . '"');
        }

        return hash(self::TYPE_TO_ALGO_NAME[$type], $salt . $value);
    }
}
