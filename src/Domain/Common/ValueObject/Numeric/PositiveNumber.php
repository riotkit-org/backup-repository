<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Numeric;

use App\Domain\Common\ValueObject\BaseValueObject;

class PositiveNumber extends BaseValueObject implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $value;

    public function __construct(int $number)
    {
        $this->setValue($number);
    }

    protected function setValue($number): void
    {
        if ($number < 1) {
            $exceptionType = static::getExceptionType();
            throw new $exceptionType('Number cannot be 0 or negative, got "' . $number . '"');
        }

        $this->value = $number;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isSameAs(PositiveNumber $number): bool
    {
        return $number->getValue() === $this->getValue();
    }

    public function isHigherThan(PositiveNumber $number): bool
    {
        return $this->getValue() > $number->getValue();
    }

    public function isHigherThanOrEqual(PositiveNumber $number): bool
    {
        return $this->getValue() >= $number->getValue();
    }

    public function isLessThan(PositiveNumber $number): bool
    {
        return $this->getValue() < $number->getValue();
    }

    public function isLessThanOrEqual(PositiveNumber $number): bool
    {
        return $this->getValue() <= $number->getValue();
    }

    public function isZero(): bool
    {
        return $this->getValue() === 0;
    }

    public function isHigherThanInteger(int $num): bool
    {
        return $this->getValue() > 0;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }
}
