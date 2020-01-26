<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Service\Client;

use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Exception\ReplicationException;
use App\Domain\Replication\Service\Client\RemoteStreamProvider;
use DateTimeImmutable;
use GuzzleHttp\Client;
use Symfony\Component\Routing\RouterInterface;

class GuzzleRemoteStreamProvider implements RemoteStreamProvider
{
    private Client $client;
    private RouterInterface $router;

    public function __construct(RouterInterface $router, Client $httpClient)
    {
        $this->router  = $router;
        $this->client  = $httpClient;
    }

    /**
     * Fetches a replication stream from the primary server
     *
     * @param int                    $limit
     * @param DateTimeImmutable|null $since
     * @param bool                   $showExampleData
     *
     * @return ReplicationLogEntry[]|array
     *
     * @throws ReplicationException
     */
    public function fetch(int $limit, ?DateTimeImmutable $since = null, bool $showExampleData = false): array
    {
        return \array_map(
            static function (string $row) {
                return ReplicationLogEntry::createFromArray(
                    \json_decode($row, true, 6, JSON_THROW_ON_ERROR)
                );
            },
            $this->fetchRaw($limit, $since, $showExampleData)
        );
    }

    /**
     * @param int $limit
     * @param DateTimeImmutable|null $since
     * @param bool $showExampleData
     *
     * @return array
     *
     * @throws ReplicationException
     */
    public function fetchRaw(int $limit, ?DateTimeImmutable $since = null, bool $showExampleData = false): array
    {
        // @todo: Endpoint nie obsługuje jeszcze limitu - zaimplementować
        // @todo: Zaimplementować showExampleData

        $response = $this->client->get($this->createRoute($since, $limit, $showExampleData));
        $asText   = $response->getBody()->read(($limit + 3) * 512);

        if ($response->getStatusCode() > 203) {
            throw new ReplicationException('Cannot fetch replication index, invalid response code: ' . $response->getStatusCode());
        }

        if ($response->getBody()->read(5)) {
            throw new ReplicationException('Body size overflow. The stream still has data');
        }

        $headerBodySeparatorPosition = \strpos($asText, "\n\n");

        if ($headerBodySeparatorPosition === false) {
            throw new ReplicationException('Invalid response format. Cannot find header and body separation');
        }

        $body = \ltrim(\substr($asText, $headerBodySeparatorPosition));

        return \array_filter(\explode("\n", $body));
    }

    private function createRoute(?DateTimeImmutable $since, int $limit, bool $showExampleData): string
    {
        $sinceFormatted = $since ? $since->format('Y-m-d H:i:s') : '';
        $testQueryPart  = $showExampleData ? '&example_data=true' : '';

        return $this->router->generate(
            'replication.dump_all',
            [],
            RouterInterface::ABSOLUTE_PATH
        ) . '?since=' . $sinceFormatted . '&limit=' . $limit . $testQueryPart;
    }
}
