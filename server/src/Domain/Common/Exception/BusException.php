<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

/**
 * @codeCoverageIgnore
 */
class BusException extends \Exception
{
    public const CALL_ON_NON_SINGLE_COMMAND     = 1;
    public const NO_COMMAND_REGISTERED          = 2;
    public const NO_COMMAND_RESPONDED_CORRECTLY = 3;
}
