<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;

class Email implements \JsonSerializable
{
    protected string $value;
    protected static string $field = 'email';

    /**
     * @param string $value
     * @return static
     *
     * @throws DomainInputValidationConstraintViolatedError
     */
    public static function fromString(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw DomainInputValidationConstraintViolatedError::fromString(
                static::$field,
                Errors::ERR_MSG_USER_MAIL_FORMAT_INVALID,
                Errors::ERR_USER_MAIL_FORMAT_INVALID
            );
        }

        $new = new static();
        $new->value = $value;

        return $new;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }

    public function __serialize()
    {
        return $this->getValue();
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
