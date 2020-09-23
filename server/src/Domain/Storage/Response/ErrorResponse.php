<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

use App\Domain\Common\Http;
use App\Domain\Common\Response\ErrorResponse as CommonErrorResponse;

class ErrorResponse extends CommonErrorResponse
{
    private const CODE_FILE_NOT_FOUND = 404;

    public static function createFileNotFoundResponse()
    {
        $response = new static();
        $response->status    = false;
        $response->message   = 'File not found';
        $response->httpCode  = Http::HTTP_NOT_FOUND;
        $response->errorCode = self::CODE_FILE_NOT_FOUND;

        return $response;
    }

    public static function createValidationErrorResponse(string $reason, int $code, array $context)
    {
        $response = new static();
        $response->status    = false;
        $response->message   = $reason;
        $response->httpCode  = Http::HTTP_INVALID_REQUEST;
        $response->errorCode = $code;
        $response->context   = $context;

        return $response;
    }

    public static function createServerErrorResponse(int $code)
    {
        $response = new static();
        $response->status    = false;
        $response->message   = 'Server error';
        $response->httpCode  = Http::HTTP_SERVER_ERROR;
        $response->errorCode = $code;

        return $response;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
