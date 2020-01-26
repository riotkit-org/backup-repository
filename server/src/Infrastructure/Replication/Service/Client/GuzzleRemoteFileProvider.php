<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Service\Client;

use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Service\Client\RemoteFileProvider;
use App\Domain\Replication\Service\Client\RemoteStreamProvider;
use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client;
use Symfony\Component\Routing\RouterInterface;

class GuzzleRemoteFileProvider implements RemoteFileProvider
{
    private Client $client;
    private RouterInterface $router;

    public function __construct(RouterInterface $router, Client $httpClient)
    {
        $this->router = $router;
        $this->client = $httpClient;
    }

    public function fetch(string $fileName)
    {
        $response = $this->client->get(
            $this->router->generate('replication.files.content.fetch', ['fileName' => $fileName])
        );

        return $response->getBody()->detach();
    }
}
