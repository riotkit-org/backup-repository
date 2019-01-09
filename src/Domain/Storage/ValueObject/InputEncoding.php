<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

use App\Domain\Common\ValueObject\BaseValueObject;

class InputEncoding extends BaseValueObject
{
    private const CHOICES = [
        'base64', 'plain', '', null
    ];

    /**
     * @var string|null
     */
    protected $value;

    public function __construct(?string $value)
    {
        if (!\in_array($value, static::CHOICES, true)) {
            $exceptionClass = static::getExceptionType();
            throw new $exceptionClass('Invalid input encoding type');
        }

        if ($value === 'plain') {
            $value = null;
        }

        $this->value = $value;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    public function isBase64(): bool
    {
        return $this->value === 'base64';
    }

    public function thereIsNoEncoding(): bool
    {
        return !$this->value;
    }
}
