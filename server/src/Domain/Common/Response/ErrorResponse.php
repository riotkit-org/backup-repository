<?php declare(strict_types=1);

namespace App\Domain\Common\Response;

abstract class ErrorResponse implements Response
{
    protected ?bool    $status    = false;
    protected          $errorCode = null;
    protected ?int     $httpCode  = null;
    protected ?array   $errors    = [];

    /**
     * Depends on response type:
     *   In case of validation failure - a dictionary of fields with maximum limits that cannot be reached
     *
     * @var array|null
     */
    protected ?array  $context   = [];
    protected ?string $message   = '';

    public function jsonSerialize(): array
    {
        return [
            'status'     => $this->status !== null ? $this->status : ($this->httpCode <= 399),
            'error_code' => $this->errorCode,
            'http_code'  => $this->httpCode,
            'errors'     => $this->errors,
            'context'    => $this->context,
            'message'    => $this->message
        ];
    }

    public function isSuccess(): bool
    {
        return false;
    }
}
