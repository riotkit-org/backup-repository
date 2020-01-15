<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Service\Client;

use App\Domain\Common\Service\Versioning;
use App\Domain\Replication\DTO\File;
use App\Domain\Replication\Service\Client\RemoteStreamFetcher;
use GuzzleHttp\Client;
use Symfony\Component\Routing\RouterInterface;

class GuzzleRemoteStreamFetcher implements RemoteStreamFetcher
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router, Versioning $versioning, string $primaryServerUrl, string $token)
    {
        $this->router  = $router;
        $this->client  = new Client(['base_uri' => $primaryServerUrl, 'headers' => [
            'Token'      => $token,
            'User-Agent' => 'FileRepository/' . $versioning->getVersion()
        ]]);
    }

    /**
     * Fetches a replication stream from the primary server
     *
     * @param int $limit
     * @param \DateTime|null $since
     *
     * @return File[]
     *
     * @throws \Exception
     */
    public function fetch(int $limit, ?\DateTime $since = null): array
    {
        $response = $this->client->get($this->createRoute($since, $limit));
        $asText   = $response->getBody()->read(($limit + 3) * 512);

        if ($response->getStatusCode() > 203) {
            throw new \Exception('Cannot fetch replication index, invalid response code: ' . $response->getStatusCode());
        }

        if ($response->getBody()->read(5)) {
            throw new \Exception('Body size overflow. The stream still has data');
        }

        $headerBodySeparatorPosition = \strpos($asText, "\n\n");

        if ($headerBodySeparatorPosition === false) {
            throw new \Exception('Invalid response format. Cannot find header and body separation');
        }

        $body = \ltrim(\substr($asText, $headerBodySeparatorPosition));
        $entries = \array_filter(\explode("\n", $body));

        return \array_map(
            function (string $row) {
                $asArray = \json_decode($row, true);
                return File::fromArray($asArray);
            },
            $entries
        );
    }

    private function createRoute(?\DateTime $since, int $limit): string
    {
        return $this->router->generate(
            'replication.dump_all',
            ['since' => $since, 'limit' => $limit],
            RouterInterface::ABSOLUTE_PATH
        );
    }
}
