<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

class DomainInputValidationConstraintViolatedError extends ApplicationException implements \JsonSerializable
{
    protected string $field;

    public static function fromString(string $field, string $message, int $code)
    {
        $new = new static();
        $new->message = $message;
        $new->code    = $code;
        $new->field   = $field;

        return $new;
    }

    public function jsonSerialize(): array
    {
        return ['field' => $this->field, 'message' => $this->message, 'code' => $this->code];
    }
}
