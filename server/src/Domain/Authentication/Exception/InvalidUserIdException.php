<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;
use Throwable;

class InvalidUserIdException extends DomainInputValidationConstraintViolatedError
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(
            Errors::ERR_MSG_USERID_FORMAT_INVALID,
            Errors::ERR_USERID_FORMAT_INVALID,
            $previous
        );

        $this->field = 'id';
    }
}
