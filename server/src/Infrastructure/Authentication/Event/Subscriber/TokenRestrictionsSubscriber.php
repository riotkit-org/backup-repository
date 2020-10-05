<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Event\Subscriber;

use App\Domain\Authentication\Entity\User;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class TokenRestrictionsSubscriber implements EventSubscriberInterface
{
    public const EVENT_PRIORITY = -30;

    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            'security.interactive_login' => [
                ['handleRequestRestrictions', self::EVENT_PRIORITY]
            ]
        ];
    }

    public function handleRequestRestrictions(InteractiveLoginEvent $event)
    {
        $request   = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');
        $ip        = $request->getClientIp();
        $user      = $event->getAuthenticationToken() ? $event->getAuthenticationToken()->getUser() : null;

        if (($user instanceof User && !$user->isValid($userAgent, $ip))) {
            $this->tokenStorage->setToken(
                new TokenTransport('anonymous', new User())
            );
            return;
        }
    }
}
