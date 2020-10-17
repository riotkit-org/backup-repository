<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Numeric;

use App\Domain\Common\Exception\CommonValueException;

class PositiveNumberOrZero extends PositiveNumber
{
    protected function setValue($number): void
    {
        if ($number < 0) {
            throw CommonValueException::fromNumberCannotBeNegative($number);
        }

        $this->value = $number;
    }
}
