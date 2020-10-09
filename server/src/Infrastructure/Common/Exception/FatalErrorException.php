<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Exception;

use App\Domain\Common\Exception\ApplicationException;
use App\Domain\Errors;

class FatalErrorException extends ApplicationException
{
    /**
     * @return static
     */
    public static function fromInternalServerError()
    {
        return new static(
            Errors::ERR_MSG_REQUEST_INTERNAL_SERVER_ERROR,
            Errors::ERR_REQUEST_INTERNAL_SERVER_ERROR
        );
    }
}
