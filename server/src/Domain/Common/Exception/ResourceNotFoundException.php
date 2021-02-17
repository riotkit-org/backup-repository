<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

use App\Domain\Errors;

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

    public function jsonSerialize(): array
    {
        return [
            'error' => $this->getMessage(),
            'code'  => $this->getCode(),
            'type'  => Errors::TYPE_NOT_FOUND
        ];
    }
}
