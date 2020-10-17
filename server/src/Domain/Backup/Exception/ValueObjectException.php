<?php declare(strict_types=1);

namespace App\Domain\Backup\Exception;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;

/**
 * Validation errors in ValueObjects
 */
class ValueObjectException extends DomainInputValidationConstraintViolatedError
{
}
