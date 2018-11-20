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
     * @var string
     */
    private $isDev;

    public function __construct(IncomingTokenFactory $factory, TokenStorageInterface $tokenStorage, string $isDev)
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
     * @throws AuthenticationException
     * @throws \Exception
     */
    public function handleIncomingToken(GetResponseEvent $event): void
    {
        $tokenString = $this->getTokenStringFromRequest($event->getRequest());

        // Guest at public endpoints
        if ($this->isPublicEndpoint($event->getRequest())) {
            return;
        }

        // Development token
        if ($this->isDev && $tokenString === self::TEST_TOKEN) {
            $this->handleTestToken();
            return;
        }

        try {
            $token = $this->factory->createFromEncodedString($tokenString);

        } catch (AuthenticationException $exception) {
            $event->setResponse(
                new JsonResponse('Not authorized', JsonResponse::HTTP_FORBIDDEN)
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

    /**
     * @throws \Exception
     */
    private function handleTestToken(): void
    {
        $token = new Token();
        $token->setId(self::TEST_TOKEN);
        $token->setRoles(Roles::ROLES_LIST);

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
