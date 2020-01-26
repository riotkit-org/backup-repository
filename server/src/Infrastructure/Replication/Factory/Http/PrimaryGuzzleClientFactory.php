<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Factory\Http;

use App\Domain\Common\Service\Versioning;
use GuzzleHttp\Client;

class PrimaryGuzzleClientFactory
{
    public static function createClient(Versioning $versioning, string $primaryServerUrl, string $token): Client
    {
        return new Client(['base_uri' => $primaryServerUrl, 'headers' => [
            'Token'      => $token,
            'User-Agent' => 'FileRepository/' . $versioning->getVersion()
        ]]);
    }
}
