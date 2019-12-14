<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

/**
 * @codeCoverageIgnore
 */
interface CommonValidationException extends \Throwable
{
    public function getFields(): array;
}
