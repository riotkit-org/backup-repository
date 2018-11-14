<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Manager;

use App\Domain\Common\Manager\StateManager;
use App\Domain\Storage\Provider\HttpDownloadProvider;
use App\Domain\Storage\ValueObject\Stream;
use App\Domain\Storage\ValueObject\Url;
use GuzzleHttp\Client;

class DownloadViaGuzzleProvider implements HttpDownloadProvider
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(StateManager $state)
    {
        $this->client = $state->generateProxy(new Client(), 'http');
    }

    public function getStreamFromUrl(Url $url): Stream
    {
        return new Stream($this->client->get($url->getValue())->getBody()->detach());
    }
}
