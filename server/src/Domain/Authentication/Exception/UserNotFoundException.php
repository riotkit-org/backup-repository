<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;

class UserNotFoundException extends DomainInputValidationConstraintViolatedError
{
    public static function fromNoLongerFoundCause()
    {
        return static::fromString(
            'email',
            Errors::ERR_MSG_USER_NOT_FOUND_BY_EMAIL,
            Errors::ERR_USER_NOT_FOUND_BY_EMAIL
        );
    }
}