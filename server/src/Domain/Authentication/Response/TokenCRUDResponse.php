<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Common\Http;
use App\Domain\Common\Response\NormalResponse;

class TokenCRUDResponse extends NormalResponse
{
    protected ?Token $token = null;

    public static function createTokenDeletedResponse(Token $token): TokenCRUDResponse
    {
        $response = new TokenCRUDResponse();
        $response->status   = true;
        $response->exitCode = Http::HTTP_OK;
        $response->token    = $token;
        $response->message  = 'Token was deleted';

        return $response;
    }

    public static function createTokenCreatedResponse(Token $token): TokenCRUDResponse
    {
        $response = new TokenCRUDResponse();
        $response->status   = true;
        $response->exitCode = Http::HTTP_CREATED;
        $response->token    = $token;
        $response->message  = 'Token created';

        return $response;
    }

    public static function createTokenFoundResponse(Token $token)
    {
        $response = new TokenCRUDResponse();
        $response->status   = true;
        $response->exitCode = Http::HTTP_OK;
        $response->token    = $token;
        $response->message  = 'Token found';

        return $response;
    }

    public static function createTokenNotFoundResponse(): self
    {
        $response = new static();
        $response->status   = false;
        $response->exitCode = Http::HTTP_NOT_FOUND;
        $response->token    = null;
        $response->message  = 'Token not found';

        return $response;
    }

    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $base['token'] = $this->token;

        return $base;
    }

    public function getTokenId(): ?string
    {
        return $this->token->getId();
    }

    public function getHttpCode(): int
    {
        return $this->exitCode;
    }
}
