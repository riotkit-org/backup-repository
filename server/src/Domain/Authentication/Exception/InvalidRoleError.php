<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\ApplicationException;
use App\Domain\Errors;
use Throwable;

class InvalidRoleError extends ApplicationException
{
    public function __construct(string $roleName, Throwable $previous = null)
    {
        parent::__construct(
            str_replace('{{ role }}', $roleName, Errors::ERR_MSG_SECURITY_INVALID_ROLE),
            Errors::ERR_SECURITY_INVALID_ROLE,
            $previous
        );
    }
}
