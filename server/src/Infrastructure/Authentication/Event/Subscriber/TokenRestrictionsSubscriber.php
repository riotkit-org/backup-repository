<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Event\Subscriber;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Factory\IncomingUserFactory;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenRestrictionsSubscriber
{
    // @todo: Launch event after user is already logged in (after JWT parsing)
    public const EVENT_PRIORITY = 0;

    private IncomingUserFactory $factory;
    private TokenStorageInterface $tokenStorage;

    public function __construct(IncomingUserFactory $factory, TokenStorageInterface $tokenStorage)
    {
        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
    }

    // @todo: Attach to event subscriber

    public function handle(RequestEvent $event)
    {
        $request   = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');
        $ip        = $request->getClientIp();
        $user      = $request->getUser();

        if (($user instanceof User && !$user->isValid($userAgent, $ip)) || !$user instanceof User) {
            $this->tokenStorage->setToken(
                new TokenTransport('anonymous', new User())
            );
            return;
        }
    }
}
