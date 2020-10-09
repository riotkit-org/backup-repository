<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

use App\Domain\Errors;

class ApplicationException extends \Exception implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'error' => $this->getMessage(),
            'code'  => $this->getCode(),
            'type'  => Errors::TYPE_APP_FATAL_ERROR
        ];
    }

    public function getHttpCode(): int
    {
        return 500;
    }

    public function canBeDisplayedPublic(): bool
    {
        return false;
    }
}
