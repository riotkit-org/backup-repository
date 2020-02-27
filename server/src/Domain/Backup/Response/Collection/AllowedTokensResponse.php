<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Collection;

use App\Domain\Common\Http;

class AllowedTokensResponse implements \JsonSerializable
{
    private string $status;
    private int    $exitCode;
    private ?int   $errorCode;
    private ?array $errors;
    private array  $tokens;

    public static function createSuccessfulResponse(array $tokens, int $status = 201): AllowedTokensResponse
    {
        $new = new static();
        $new->status     = 'OK';
        $new->errorCode  = null;
        $new->exitCode   = $status;
        $new->errors     = [];
        $new->tokens     = $tokens;

        return $new;
    }

    public static function createWithNotFoundError(): AllowedTokensResponse
    {
        $new = new static();
        $new->status    = 'Object not found';
        $new->exitCode  = Http::HTT_NOT_FOUND;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'error_code' => $this->errorCode,
            'http_code'  => $this->exitCode,
            'errors'     => $this->errors,
            'tokens'     => $this->tokens
        ];
    }

    public function getHttpCode(): int
    {
        return $this->exitCode;
    }
}
