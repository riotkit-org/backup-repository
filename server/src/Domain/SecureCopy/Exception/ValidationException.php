<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Exception;

use App\Domain\Common\Exception\CommonValidationException;
use Throwable;

class ValidationException extends \Exception implements CommonValidationException
{
    private string $fieldName;

    public function __construct(string $message, string $fieldName, int $code = 0, ?Throwable $previous = null)
    {
        $this->fieldName = $fieldName;

        parent::__construct($message, $code, $previous);
    }

    public function getFields(): array
    {
        return [$this->fieldName => $this->getMessage()];
    }
}
