<?php declare(strict_types=1);

namespace App\Domain\Replication\Exception;

use App\Domain\Common\Exception\CommonValidationException;
use Throwable;

class ValidationException extends \Exception implements CommonValidationException
{
    /**
     * @var string
     */
    private $fieldName;

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
