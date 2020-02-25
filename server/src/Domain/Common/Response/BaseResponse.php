<?php declare(strict_types=1);

namespace App\Domain\Common\Response;

abstract class BaseResponse implements \JsonSerializable
{
    protected ?bool    $status   = null;
    protected ?string $errorCode = null;
    protected ?int    $exitCode  = null;
    protected ?array  $errors    = [];
    protected ?array  $context   = [];
    protected ?string $message   = '';

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status !== null ? $this->status : ($this->exitCode <= 299),
            'error_code' => $this->errorCode,
            'http_code'  => $this->exitCode,
            'errors'     => $this->errors,
            'context'    => $this->context,
            'message'    => $this->message
        ];
    }
}
