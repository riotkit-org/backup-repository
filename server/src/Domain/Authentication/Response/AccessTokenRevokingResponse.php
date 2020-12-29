<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Common\Http;
use App\Domain\Common\Response\NormalResponse;

class AccessTokenRevokingResponse extends NormalResponse
{
    /**
     * @return static
     */
    public static function createRevokedResponse()
    {
        $response = new static();
        $response->status   = true;
        $response->message  = 'Access was revoked';
        $response->httpCode = Http::HTTP_OK;

        return $response;
    }
}
