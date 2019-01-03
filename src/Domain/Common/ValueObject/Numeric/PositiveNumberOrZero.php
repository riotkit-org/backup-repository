<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Numeric;

class PositiveNumberOrZero extends PositiveNumber
{
    protected function setValue($number): void
    {
        if ($number < 0) {
            $exceptionType = static::getExceptionType();
            throw new $exceptionType('number_cannot_be_negative_value');
        }

        $this->value = $number;
    }
}
