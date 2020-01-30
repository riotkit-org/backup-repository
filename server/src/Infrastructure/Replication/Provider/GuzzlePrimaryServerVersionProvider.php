<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Provider;

use App\Domain\Replication\Provider\PrimaryServerVersionProvider;
use GuzzleHttp\Client;

class GuzzlePrimaryServerVersionProvider implements PrimaryServerVersionProvider
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getVersion(): string
    {
        $response = $this->client->get('/version');

        return \json_decode($response->getBody()->getContents(), true);
    }
}
