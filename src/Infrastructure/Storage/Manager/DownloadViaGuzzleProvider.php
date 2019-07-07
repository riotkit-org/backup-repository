<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Manager;

use App\Domain\Common\Manager\StateManager;
use App\Domain\Storage\Exception\FileRetrievalError;
use App\Domain\Storage\Provider\HttpDownloadProvider;
use App\Domain\Storage\ValueObject\Stream;
use App\Domain\Storage\ValueObject\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class DownloadViaGuzzleProvider implements HttpDownloadProvider
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(StateManager $state, int $httpTimeout)
    {
        $options = [
            'timeout'         => $httpTimeout,
            'allow_redirects' => true
        ];

        $this->client = $state->generateProxy(new Client($options), 'http');
    }

    public function getStreamFromUrl(Url $url): Stream
    {
        try {
            return new Stream($this->client->get($url->getValue())->getBody()->detach());

        } catch (ConnectException $exception) {
            $msg = $exception->getMessage();

            if (stripos($msg, 'Could not resolve host') !== false
                || stripos($msg, 'cURL error 6') !== false
                || stripos($msg, 'Resolving timed out') !== false
                || stripos($msg, 'cURL error 28') !== false
                || stripos($msg, 'Connection refused') !== false
                || stripos($msg, 'Failed to connect to') !== false
                || stripos($msg, 'cURL error 7' !== false)) {
                throw new FileRetrievalError('URL is not accessible, probably the domain is not valid',
                    FileRetrievalError::URL_SERVER_NOT_REACHABLE);
            }

            if (stripos($msg, 'Not found') !== false) {
                throw new FileRetrievalError('URL is not valid, file not found',
                    FileRetrievalError::URL_NOT_FOUND);
            }

            throw new FileRetrievalError('Cannot access remote URL, unrecognized reason: ' . $msg,
                FileRetrievalError::URL_GENERAL_ERROR);
        }
    }
}
