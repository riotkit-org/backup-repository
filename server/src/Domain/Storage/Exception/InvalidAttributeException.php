<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

class InvalidAttributeException extends \Exception
{
    public const ERROR_DUPLICATED_ATTRIBUTE_NAME = 70001;

    public static function createDuplicatedAttributeException(string $name): InvalidAttributeException
    {
        return new static('Duplicated attribute name "' . $name . '"', self::ERROR_DUPLICATED_ATTRIBUTE_NAME);
    }
}
