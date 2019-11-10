<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\ValueObjectException;

abstract class BaseValueObject
{
    protected static function getExceptionType(): string
    {
        return ValueObjectException::class;
    }
}
