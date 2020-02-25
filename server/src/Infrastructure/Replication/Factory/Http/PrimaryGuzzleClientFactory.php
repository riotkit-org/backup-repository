<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Factory\Http;

use App\Domain\Common\Service\Versioning;
use App\Infrastructure\Replication\Service\HttpClient;

class PrimaryGuzzleClientFactory
{
    public static function createClient(Versioning $versioning, string $primaryServerUrl, string $token): HttpClient
    {
        return new HttpClient(['base_uri' => $primaryServerUrl, 'headers' => [
            'Token'      => $token,
            'User-Agent' => 'FileRepository/' . $versioning->getVersion()
        ]]);
    }
}
