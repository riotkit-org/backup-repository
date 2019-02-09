<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Event\Subscriber;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\IncomingTokenFactory;
use App\Domain\Roles;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenSubscriber implements EventSubscriberInterface
{
    public const EVENT_PRIORITY = 0;

    private const TEST_TOKEN = 'test-token-full-permissions';

    /**
     * @var IncomingTokenFactory
     */
    private $factory;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var bool
     */
    private $isDev;

    public function __construct(IncomingTokenFactory $factory, TokenStorageInterface $tokenStorage, bool $isDev)
    {
        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
        $this->isDev = $isDev;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => [
                ['handleIncomingToken', self::EVENT_PRIORITY]
            ]
        ];
    }

    /**
     * @param GetResponseEvent $event
     *
     * @throws \Exception
     */
    public function handleIncomingToken(GetResponseEvent $event): void
    {
        // workaround: sometimes the event is fired twice by the Symfony, second time it has nulled attributes
        if ($event->getRequest()->attributes->get('_route') === null) {
            return;
        }

        $tokenString = $this->getTokenStringFromRequest($event->getRequest());

        // Development token
        if ($this->isDev && ($tokenString === self::TEST_TOKEN || $this->isProfilerRoute($event->getRequest()))) {
            $this->handleTestToken();
            return;
        }

        // Guest at public endpoints
        if ($this->isPublicEndpoint($event->getRequest())) {
            $this->tokenStorage->setToken(
                new TokenTransport('anonymous', new Token())
            );
            return;
        }

        try {
            $token = $this->factory->createFromEncodedString($tokenString);

        } catch (AuthenticationException $exception) {
            $event->setResponse(
                new JsonResponse(
                    [
                        'status'     => 'Not authorized, no valid token present at all',
                        'error_code' => 403,
                        'http_code'  => 403
                    ],
                    JsonResponse::HTTP_FORBIDDEN
                )
            );

            return;
        }

        if (!$token->getId()) {
            $this->tokenStorage->setToken(new TokenTransport($tokenString, $token));

            return;
        }

        $this->tokenStorage->setToken(new TokenTransport($token->getId(), $token));
    }

    private function isPublicEndpoint(Request $request): bool
    {
        return !($request->attributes->get('_route_params')['_secured'] ?? true);
    }

    private function isProfilerRoute(Request $request): bool
    {
        return \strpos($request->getPathInfo(), '/_profiler/') === 0;
    }

    /**
     * @throws \Exception
     */
    private function handleTestToken(): void
    {
        $token = new Token();
        $token->setId(self::TEST_TOKEN);
        $token->setRoles([Roles::ROLE_ADMINISTRATOR]);

        $this->tokenStorage->setToken(
            new TokenTransport(self::TEST_TOKEN, $token)
        );
    }

    private function getTokenStringFromRequest(Request $request): string
    {
        if ($request->query->has('_token')) {
            return $request->query->get('_token');
        }

        if ($request->headers->get('token')) {
            return $request->query->get('token');
        }

        if ($request->headers->get('x-auth-token')) {
            return $request->query->get('x-auth-token');
        }

        return '';
    }
}
