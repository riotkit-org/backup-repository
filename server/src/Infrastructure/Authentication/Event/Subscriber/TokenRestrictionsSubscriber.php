<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Event\Subscriber;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Repository\AccessTokenAuditRepository;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class TokenRestrictionsSubscriber implements EventSubscriberInterface
{
    public const EVENT_PRIORITY = -30;

    private TokenStorageInterface $tokenStorage;
    private AccessTokenAuditRepository $accessTokenAuditRepository;
    private JWTEncoderInterface $encoder;

    public function __construct(TokenStorageInterface $tokenStorage, AccessTokenAuditRepository $accessTokenAuditRepository,
                                JWTEncoderInterface $encoder)
    {
        $this->tokenStorage = $tokenStorage;
        $this->accessTokenAuditRepository = $accessTokenAuditRepository;
        $this->encoder = $encoder;
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
        $jwt       = $this->getTokenFromRequest($event->getRequest());

        // @todo: Support for DNS resolving
        // User account can be expired, deactivated or the UserAgent/IP-Address can be not matching
        if (($user instanceof User && !$user->isValid($userAgent, $ip))) {
            $this->tokenStorage->setToken(
                new TokenTransport('anonymous', new User())
            );
            return;
        }

        // JWT token can be manually revoked, or just expired
        if (!$this->accessTokenAuditRepository->isActiveToken($jwt)) {
            $this->tokenStorage->setToken(
                new TokenTransport('anonymous', new User())
            );
            return;
        }

        // limit the roles on the user object
        if ($this->tokenStorage->getToken() instanceof JWTUserToken) {
            $roles = $this->encoder->decode($jwt)['roles'] ?? [];

            $this->tokenStorage->setToken(
                new JWTUserToken(
                    $roles,
                    $this->tokenStorage->getToken()->getUser()->withRoles($roles),
                    $jwt
                )
            );
        }
    }


    private function getTokenFromRequest(Request $request): ?string
    {
        $auth = explode(' ', $request->headers->get('authorization', ''));

        return $auth[1] ?? '';
    }
}
