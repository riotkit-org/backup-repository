<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Numeric;

use App\Domain\Common\Exception\CommonValueException;
use App\Domain\Common\ValueObject\BaseValueObject;

class PositiveNumber extends BaseValueObject implements \JsonSerializable
{
    protected int $value;

    public function __construct(int $number)
    {
        $this->setValue($number);
    }

    protected function setValue($number): void
    {
        if ($number < 1) {
            throw CommonValueException::fromNumberCannotBeNegative($number);
        }

        $this->value = $number;
    }

    public function getValue(): int
    {
        return (int) $this->value;
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
        return $this->getValue() > $num;
    }

    /**
     * @return static
     */
    public function incrementVersion()
    {
        $new = clone $this;
        ++$new->value;

        return $new;
    }

    /**
     * @param int $num
     *
     * @return static
     *
     * @throws CommonValueException
     */
    public function addInteger(int $num)
    {
        $new = clone $this;
        $new->value += $num;

        if ($new->value < 0) {
            throw CommonValueException::fromSumOperationResultNoLongerPositiveNumberCause();
        }

        return $new;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }
}
