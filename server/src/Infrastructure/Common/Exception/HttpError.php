<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Exception;

use App\Domain\Common\Exception\ApplicationException;
use App\Domain\Errors;

class HttpError extends ApplicationException
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

    /**
     * @return static
     */
    public static function fromNotFoundError()
    {
        return new static(
            Errors::ERR_MSG_REQUEST_NOT_FOUND,
            Errors::ERR_REQUEST_NOT_FOUND
        );
    }

    /**
     * @return static
     */
    public static function fromAccessDeniedError()
    {
        return new static(
            Errors::ERR_MSG_REQUEST_ACCESS_DENIED,
            Errors::ERR_REQUEST_ACCESS_DENIED
        );
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        if ($this->getCode() === Errors::ERR_REQUEST_NOT_FOUND) {
            $data['type'] = Errors::TYPE_NOT_FOUND;
        }

        return $data;
    }
}
