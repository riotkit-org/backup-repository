<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Service;

use App\Domain\Replication\Exception\PrimaryLinkNotConfiguredError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

class HttpClient extends Client
{
    /**
     * @param string $method
     * @param array $args
     *
     * @return PromiseInterface
     *
     * @throws PrimaryLinkNotConfiguredError
     */
    public function __call($method, $args)
    {
        $this->assertPrimaryConfigured();

        try {
            return parent::__call($method, $args);

        } catch (RequestException $exception) {
            $this->handleRequestException($exception);
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws PrimaryLinkNotConfiguredError
     */
    public function request($method, $uri = '', array $options = [])
    {
        $this->assertPrimaryConfigured();

        try {
            return parent::request($method, $uri, $options);

        } catch (RequestException $exception) {
            $this->handleRequestException($exception);
        }
    }

    /**
     * @param RequestException $exception
     *
     * @throws PrimaryLinkNotConfiguredError
     */
    private function handleRequestException(RequestException $exception): void
    {
        if ($exception->getCode() === 403) {
            throw new PrimaryLinkNotConfiguredError('Cannot authenticate with primary server. Is the token valid?', 0, $exception);
        }

        if ($exception->getCode() >= 500) {
            throw new PrimaryLinkNotConfiguredError('The primary server has internal server error', 0, $exception);
        }

        throw $exception;
    }

    /**
     * @throws PrimaryLinkNotConfiguredError
     */
    private function assertPrimaryConfigured(): void
    {
        $config = $this->getConfig();

        /**
         * @var Uri $baseUrl
         */
        $baseUrl = $config['base_uri'];

        if (!$baseUrl->getHost()) {
            throw new PrimaryLinkNotConfiguredError('Primary server not configured, check documentation for hints');
        }
    }
}
