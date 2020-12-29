<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

/**
 * @codeCoverageIgnore
 */
class ResourceNotFoundException extends RequestException
{
    /**
     * @param string $message
     *
     * @return static
     */
    public static function createFromMessage(string $message)
    {
        return new static($message, 404);
    }
}
