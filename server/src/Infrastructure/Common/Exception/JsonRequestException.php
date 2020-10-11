<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Exception;

use App\Domain\Common\Exception\ApplicationException;
use App\Domain\Errors;
use \Throwable;

class JsonRequestException extends ApplicationException
{
    public static function fromJsonToFormMappingError(Throwable $error)
    {
        return new static(
            str_replace('{{ details }}', $error->getMessage(), Errors::ERR_MSG_REQUEST_CANNOT_PARSE_JSON),
            Errors::ERR_REQUEST_CANNOT_PARSE_JSON
        );
    }
}
