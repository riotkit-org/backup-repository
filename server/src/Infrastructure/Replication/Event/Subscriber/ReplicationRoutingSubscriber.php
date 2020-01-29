<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Event\Subscriber;

use App\Domain\Replication\Provider\ConfigurationProvider;
use App\Domain\Replication\ReplicaDomain;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use GuzzleHttp\Client;

/**
 * Forwards requests to the PRIMARY server in REPLICATION MODE
 * Note: Only requests for routes marked with label '_access_in_replica_mode: redirect-to-primary' are forwarded
 */
class ReplicationRoutingSubscriber implements EventSubscriberInterface
{
    public const EVENT_PRIORITY = 10;

    private ConfigurationProvider $configurationProvider;
    private LoggerInterface       $logger;
    private bool                  $isDev;

    public function __construct(ConfigurationProvider $configurationProvider, LoggerInterface $logger, bool $isDev)
    {
        $this->configurationProvider = $configurationProvider;
        $this->logger                = $logger;
        $this->isDev                 = $isDev;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => [
                ['handleRoute', self::EVENT_PRIORITY]
            ]
        ];
    }

    /**
     * @param RequestEvent $event
     *
     * @throws \Exception
     */
    public function handleRoute(RequestEvent $event): void
    {
        if (!$this->configurationProvider->isNodeConfiguredAsReplica()) {
            return;
        }

        $request     = $event->getRequest();
        $replicaMode = $request->attributes->get('_route_params')['_access_in_replica_mode'];

        if (!$replicaMode) {
            throw new \LogicException('Endpoint has not implemented "_access_in_replica_mode" attribute. Please feel free to report a bug.');
        }

        if ($replicaMode === ReplicaDomain::REQUEST_MODE_FORWARD) {
            $event->setResponse($this->forwardRoute($request));
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    private function forwardRoute(Request $request): Response
    {
        $url = $this->configurationProvider->getPrimaryUrl() . $request->getRequestUri();

        $this->logger->debug('[Replication] Forwarding route to ' . $url);

        // prepare headers
        $headers = $request->headers->all();
        $headers['x-forwarded-by-file-repository'] = ['true'];
        $headers['x-forwarded-for']                = [$request->getClientIp()];
        unset(
            $headers['host'],
            $headers['upgrade-insecure-requests'],
            $headers['content-length'],
            $headers['x-php-ob-level'],
            $headers['connection']
        );

        $client = new Client();
        $response = $client->request($request->getMethod(), $url, [
            'headers'     => $headers,
            'body'        => $request->getContent(true),
            'http_errors' => false
        ]);
        $customHeaders = [];

        if ($this->isDev) {
            $customHeaders['FileRepository-Forwarded-From-Primary'] = 'true';
        }

        return new StreamedResponse(
            function () use ($response) {
                $out = $response->getBody()->detach();

                while (!feof($out)) {
                    print(fread($out, 1024 * 1024 * 8));
                }

                fclose($out);
                ob_flush();
            },
            $response->getStatusCode(),
            \array_merge($response->getHeaders(), $customHeaders)
        );
    }
}
