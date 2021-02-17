<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;

class UserAlreadyExistsException extends DomainInputValidationConstraintViolatedError
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(
            Errors::ERR_MSG_USER_EXISTS,
            static::getUserAlreadyExistsCode(),
            $previous
        );

        $this->field = 'id';
    }

    public static function getUserAlreadyExistsCode(): int
    {
        return Errors::ERR_USER_EXISTS;
    }
}
