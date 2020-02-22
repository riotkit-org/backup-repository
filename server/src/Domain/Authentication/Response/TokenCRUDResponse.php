<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Common\Http;
use App\Domain\Common\Response\BaseResponse;

class TokenCRUDResponse extends BaseResponse
{
    protected ?Token $token = null;

    public static function createTokenDeletedResponse(Token $token): TokenCRUDResponse
    {
        $response = new TokenCRUDResponse();
        $response->status   = true;
        $response->exitCode = Http::HTTP_ACCEPTED;
        $response->token    = $token;
        $response->message  = 'Token was deleted';

        return $response;
    }

    public static function createTokenCreatedResponse(Token $token): TokenCRUDResponse
    {
        $response = new TokenCRUDResponse();
        $response->status   = true;
        $response->exitCode = Http::HTTP_ACCEPTED;
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
}
