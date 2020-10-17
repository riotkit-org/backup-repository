<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;

class SearchFormException extends DomainInputValidationConstraintViolatedError
{
    /**
     * @return static
     */
    public static function fromQueryLimitTooHighCause()
    {
        return static::fromString(
            'query',
            Errors::ERR_MSG_REQUEST_LIMIT_TOO_HIGH,
            Errors::ERR_REQUEST_LIMIT_TOO_HIGH
        );
    }

    /**
     * @return static
     */
    public static function fromQueryLimitCannotBeNegativeCause()
    {
        return static::fromString(
            'query',
            Errors::ERR_MSG_REQUEST_LIMIT_TOO_LOW,
            Errors::ERR_REQUEST_LIMIT_TOO_LOW
        );
    }

    /**
     * @return static
     */
    public static function fromPageCannotBeNegativeCause()
    {
        return static::fromString(
            'page',
            Errors::ERR_MSG_REQUEST_PAGE_TOO_LOW,
            Errors::ERR_REQUEST_PAGE_TOO_LOW
        );
    }
}
