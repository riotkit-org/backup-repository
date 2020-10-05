<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Event\Subscriber;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Factory\IncomingUserFactory;
use App\Domain\Roles;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DevelopmentTokenSubscriber implements EventSubscriberInterface
{
    public const EVENT_PRIORITY = 31;

    private IncomingUserFactory $factory;
    private TokenStorageInterface $tokenStorage;
    private bool $isDev;

    public function __construct(IncomingUserFactory $factory, TokenStorageInterface $tokenStorage, bool $isDev)
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
     * @param RequestEvent $event
     *
     * @throws \Exception
     */
    public function handleIncomingToken(RequestEvent $event): void
    {
        // workaround: sometimes the event is fired twice by the Symfony, second time it has nulled attributes
        if ($event->getRequest()->attributes->get('_route') === null) {
            return;
        }

        $tokenString = $this->getTokenStringFromRequest($event->getRequest());
        $token       = null;

        // Development token
        if ($this->isDev && (Roles::isTestToken($tokenString) || $this->isProfilerRoute($event->getRequest()))) {
            $this->handleTestToken();
            return;
        }
    }

    private function isProfilerRoute(Request $request): bool
    {
        return \strpos($request->getPathInfo(), '/_profiler/') === 0
            || \strpos($request->getPathInfo(), '/_wdt') === 0;
    }

    /**
     * @throws \Exception
     */
    private function handleTestToken(): void
    {
        $token = new User();
        $token->setId(Roles::TEST_TOKEN);
        $token->setRoles(\App\Domain\Authentication\ValueObject\Roles::fromArray([Roles::ROLE_ADMINISTRATOR]));

        $this->tokenStorage->setToken(
            new TokenTransport(Roles::TEST_TOKEN, $token)
        );
    }

    private function getTokenStringFromRequest(Request $request): string
    {
        if ($request->headers->get('test-token')) {
            return (string) $request->headers->get('test-token');
        }

//        // allows to set a default viewer token on infrastructure level (eg. in fastcgi/proxy_pass)
//        if ($request->server->get('FILE_REPOSITORY_TOKEN')) {
//            return (string) $request->headers->get('FILE_REPOSITORY_TOKE');
//        }

        return '';
    }
}
