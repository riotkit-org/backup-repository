<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

use App\Domain\Errors;

/**
 * Validation exceptions raised by ValueObjects from the Common domain part
 *
 * @codeCoverageIgnore
 */
class CommonValueException extends ApplicationException
{
    public static function fromInvalidChoice(string $value, array $getChoices)
    {
        return new static(
            str_replace(['{{ actual }}', '{{ choices }}'], [$value, implode(', ', $getChoices)], Errors::ERR_MSG_COMMON_VALUE_INVALID_CHOICE),
            Errors::ERR_COMMON_VALUE_INVALID_CHOICE
        );
    }

    public static function fromNumberCannotBeNegative($number)
    {
        return new static(
            str_replace('{{ actual }}', (string) $number, Errors::ERR_MSG_NUMBER_CANNOT_BE_NEGATIVE),
            Errors::ERR_NUMBER_CANNOT_BE_NEGATIVE
        );
    }

    public static function fromSumOperationResultNoLongerPositiveNumberCause()
    {
        return new static(
            Errors::ERR_MSG_SUM_OF_TWO_NUMBERS_NO_LONGER_GIVES_POSITIVE_NUMBER,
            Errors::ERR_SUM_OF_TWO_NUMBERS_NO_LONGER_GIVES_POSITIVE_NUMBER
        );
    }

    public static function fromInvalidUrl(string $normalized, string $original)
    {
        return new static(
            str_replace(['{{ normalized }}', '{{ original }}'], [$normalized, $original], Errors::ERR_MSG_INVALID_URL),
            Errors::ERR_INVALID_URL
        );
    }

    public static function fromChecksumLengthNotMatchingCause()
    {
        return new static(
            Errors::ERR_MSG_CHECKSUM_LENGTH_DOES_NOT_MATCH,
            Errors::ERR_CHECKSUM_LENGTH_DOES_NOT_MATCH
        );
    }

    public static function fromInvalidChecksumTypeCause()
    {
        return new static(
            Errors::ERR_MSG_UNSUPPORTED_CHECKSUM_TYPE,
            Errors::ERR_UNSUPPORTED_CHECKSUM_TYPE
        );
    }

    public static function fromInvalidPermissionsSelected(string $role, array $availableRoles)
    {
        return new static(
            str_replace(['{{ role }}', '{{ available }}'], [$role, json_encode($availableRoles)],Errors::ERR_MSG_USER_ROLE_INVALID),
            Errors::ERR_USER_ROLE_INVALID
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'error' => $this->getMessage(),
            'code'  => $this->getCode(),
            'type'  => Errors::TYPE_VALIDATION_ERROR
        ];
    }

    public function canBeDisplayedPublic(): bool
    {
        return true;
    }

    public function getHttpCode(): int
    {
        return 400;
    }
}
