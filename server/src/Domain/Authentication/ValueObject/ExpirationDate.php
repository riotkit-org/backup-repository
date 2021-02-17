<?php declare(strict_types=1);

namespace App\Domain\Authentication\ValueObject;

use App\Domain\Authentication\Exception\ExpirationDateInvalidError;

class ExpirationDate
{
    public const DATE_MODIFIER_AUTO  = ['auto', 'automatic', '', null];
    public const DATE_MODIFIER_NEVER = ['never'];

    private \DateTimeImmutable $value;

    /**
     * @param string|null $modifier
     * @param string $default
     *
     * @return static
     *
     * @throws ExpirationDateInvalidError
     */
    public static function fromString(?string $modifier, string $default)
    {
        $new = new static();

        if (\in_array($modifier, static::DATE_MODIFIER_AUTO, true) || $modifier === null) {
            $new->value = (new \DateTimeImmutable())->modify($default);
            return $new;
        }

        if (\in_array($modifier, static::DATE_MODIFIER_NEVER, true)) {
            $new->value = (new \DateTimeImmutable())->modify('+90 years');
            return $new;
        }

        if (!\strtotime($modifier)) {
            throw ExpirationDateInvalidError::fromInvalidFormatCause();
        }

        try {
            $new->value = new \DateTimeImmutable($modifier);

        } catch (\Exception $exception) {
            throw ExpirationDateInvalidError::fromInvalidFormatCause();
        }

        return $new;
    }

    public function getValue(): \DateTimeImmutable
    {
        return $this->value;
    }
}
