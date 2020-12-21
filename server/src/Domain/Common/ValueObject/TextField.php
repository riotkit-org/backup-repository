<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;

class TextField implements \JsonSerializable
{
    protected string $value;
    protected static string $field        = '';
    protected static int $maxAllowedChars = 0;

    /**
     * @param string $value
     *
     * @return static
     *
     * @throws DomainInputValidationConstraintViolatedError
     */
    public static function fromString(string $value)
    {
        static::validateMaxAllowedChars($value);

        $new = new static();
        $new->value = $value;

        return $new;
    }

    /**
     * @param string $value
     *
     * @throws DomainInputValidationConstraintViolatedError
     */
    private static function validateMaxAllowedChars(string $value)
    {
        if (static::$maxAllowedChars && strlen($value) > static::$maxAllowedChars) {
            throw DomainInputValidationConstraintViolatedError::fromString(
                static::$field,
                Errors::ERR_MSG_TEXT_FIELD_TOO_LONG,
                Errors::ERR_TEXT_FIELD_TOO_LONG
            );
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->getValue();
    }
}
