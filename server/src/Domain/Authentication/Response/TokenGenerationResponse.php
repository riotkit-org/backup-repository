<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

class TokenGenerationResponse implements \JsonSerializable
{
    private string $token;

    public static function create(string $token): TokenGenerationResponse
    {
        $response = new static();
        $response->token = $token;

        return $response;
    }

    public function jsonSerialize(): array
    {
        return ['token' => $this->token];
    }
}
