<?php declare(strict_types=1);

namespace App\Domain\Authentication\ValueObject;

use App\Domain\Authentication\Exception\ValidationException;

class ExpirationDate
{
    public const DATE_MODIFIER_AUTO  = ['auto', 'automatic', '', null];
    public const DATE_MODIFIER_NEVER = ['never'];

    private \DateTimeImmutable $value;

    public static function fromString(?string $modifier, string $default)
    {
        $new = new static();

        if (\in_array($modifier, static::DATE_MODIFIER_AUTO, true) || $modifier === null) {
            $new->value = (new \DateTimeImmutable())->modify($default);
            return $new;
        }

        if (\in_array($modifier, static::DATE_MODIFIER_NEVER, true)) {
            $new->value = (new \DateTimeImmutable())->modify('+40 years');
            return $new;
        }

        if (!\strtotime($modifier)) {
            throw ValidationException::createFromFieldsList([
                'expires' => ['invalid_date_format_and_not_an_expression']
            ]);
        }

        $new->value = new \DateTimeImmutable($modifier);
        return $new;
    }

    public function getValue(): \DateTimeImmutable
    {
        return $this->value;
    }
}
