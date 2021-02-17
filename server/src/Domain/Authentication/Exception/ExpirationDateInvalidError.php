<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;

class ExpirationDateInvalidError extends DomainInputValidationConstraintViolatedError
{
    /**
     * @return static
     */
    public static function fromInvalidFormatCause()
    {
        return static::fromString(
            'expirationDate',
            Errors::ERR_MSG_EXPIRATION_DATE_INVALID_FORMAT,
            Errors::ERR_EXPIRATION_DATE_INVALID_FORMAT
        );
    }
}
