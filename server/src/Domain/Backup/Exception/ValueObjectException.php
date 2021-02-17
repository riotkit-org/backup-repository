<?php declare(strict_types=1);

namespace App\Domain\Backup\Exception;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;

/**
 * Validation errors in ValueObjects
 */
class ValueObjectException extends DomainInputValidationConstraintViolatedError
{
    /**
     * @param string $actual
     * @param array $choices
     *
     * @return static
     */
    public static function fromBackupStrategyInvalid(string $actual, array $choices)
    {
        return static::fromString(
            'strategy',
            str_replace(['{{ actual }}', '{{ choices }}'], [$actual, implode(', ', $choices)], Errors::ERR_MSG_COLLECTION_STRATEGY_INVALID),
            Errors::ERR_COLLECTION_STRATEGY_INVALID
        );
    }
}
