<?php declare(strict_types=1);

namespace App\Domain\Common\Response;

/**
 * Successful response: Status message, status code, HTTP code (for HTTP requests)
 */
abstract class NormalResponse implements Response
{
    protected ?bool  $status   = true;
    protected int    $httpCode = 200;
    protected string $message  = '';

    public function jsonSerialize(): array
    {
        return [
            'message'   => $this->message,
            'status'    => $this->status,
            'http_code' => $this->httpCode
        ];
    }

    public function isSuccess(): bool
    {
        return $this->httpCode <= 399;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
