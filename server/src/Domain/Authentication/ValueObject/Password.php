<?php declare(strict_types=1);

namespace App\Domain\Authentication\ValueObject;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;
use App\Domain\Authentication\Configuration\PasswordHashingConfiguration;

class Password implements \JsonSerializable
{
    protected string $value = '';

    /**
     * @param string                       $value
     * @param string                       $salt
     * @param PasswordHashingConfiguration $configuration
     *
     * @return Password
     *
     * @throws DomainInputValidationConstraintViolatedError
     */
    public static function fromString(string $value, string $salt, PasswordHashingConfiguration $configuration): Password
    {
        if (strlen($value) < 8) {
            throw DomainInputValidationConstraintViolatedError::fromString(
                'password',
                Errors::ERR_MSG_USER_PASSWORD_TOO_SHORT,
                Errors::ERR_USER_PASSWORD_TOO_SHORT
            );
        }

        if (strlen($value) > 1024) {
            throw DomainInputValidationConstraintViolatedError::fromString(
                'password',
                Errors::ERR_MSG_USER_PASSWORD_TOO_LONG,
                Errors::ERR_USER_PASSWORD_TOO_LONG
            );
        }

        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $value)) {
            throw DomainInputValidationConstraintViolatedError::fromString(
                'password',
                Errors::ERR_MSG_ERR_USER_PASSWORD_TOO_SIMPLE,
                Errors::ERR_USER_PASSWORD_TOO_SIMPLE
            );
        }

        if (trim($value) !== $value) {
            throw DomainInputValidationConstraintViolatedError::fromString(
                'password',
                Errors::ERR_MSG_USER_PASSWORD_WHITESPACES,
                Errors::ERR_USER_PASSWORD_WHITESPACES
            );
        }

        $new = new static();
        $salted = $salt ? $value . '{' . $salt . '}' : $value;

        $digest = hash($configuration->algorithm, $salted, true);

        for ($i = 1; $i < $configuration->iterations; ++$i) {
            $digest = hash($configuration->algorithm, $digest.$salted, true);
        }

        $new->value = bin2hex($digest);

        return $new;
    }

    public static function fromEmpty(): Password
    {
        return new static();
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * Checks if password is same
     *
     * @param Password $password
     *
     * @return bool
     */
    public function isSame(Password $password): bool
    {
        return $this->getValue() === $password->getValue();
    }
}
