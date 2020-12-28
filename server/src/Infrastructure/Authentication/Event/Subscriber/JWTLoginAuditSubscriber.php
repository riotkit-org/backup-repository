<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Event\Subscriber;

use App\Domain\Authentication\Service\Security\AccessTokenAuditRecorder;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTLoginAuditSubscriber implements EventSubscriberInterface
{
    private AccessTokenAuditRecorder $recorder;

    public function __construct(AccessTokenAuditRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::JWT_ENCODED => ['onTokenCreated', 30]
        ];
    }

    public function onTokenCreated(JWTEncodedEvent $event)
    {
        $this->recorder->record($event->getJWTString());
    }
}
