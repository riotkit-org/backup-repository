<?php declare(strict_types=1);

namespace App\Domain\Common\Response;

use App\Domain\Common\Http;
use App\Domain\Common\SharedEntity\Token;

abstract class AccessDeniedResponse implements Response
{
    protected ?Token $token      = null;
    protected ?string $message   = '';

    /**
     * @param string $message
     * @param Token|null $token
     *
     * @return static
     */
    public static function createAccessDeniedResponse(string $message = '', ?Token $token = null)
    {
        $response = new static();
        $response->token   = $token;
        $response->message = $message ?: 'Access denied. Check if your access token contains suggested roles.';

        return $response;
    }

    public function jsonSerialize(): array
    {
        return [
            'status'                      => 403,
            'message'                     => $this->message,
            'potentially_required_roles'  => $this->token->getRequestedRolesList()
        ];
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function getHttpCode(): int
    {
        return Http::HTTP_ACCESS_DENIED;
    }
}
