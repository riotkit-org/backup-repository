<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Event\Subscriber;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Repository\AccessTokenAuditRepository;
use App\Domain\Common\Exception\CommonValueException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class TokenRestrictionsSubscriber implements EventSubscriberInterface
{
    public const EVENT_PRIORITY = -30;

    public function __construct(private TokenStorageInterface $tokenStorage,
                                private AccessTokenAuditRepository $accessTokenAuditRepository,
                                private JWTEncoderInterface $encoder) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'security.interactive_login' => [
                ['handleRequestRestrictions', self::EVENT_PRIORITY]
            ]
        ];
    }

    /**
     * @param InteractiveLoginEvent $event
     * @throws AuthenticationException
     *
     * @throws CommonValueException
     * @throws JWTDecodeFailureException
     */
    public function handleRequestRestrictions(InteractiveLoginEvent $event): void
    {
        $request   = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');
        $ip        = $request->getClientIp();
        $user      = $event->getAuthenticationToken() ? $event->getAuthenticationToken()->getUser() : null;
        $jwt       = $this->getTokenFromRequest($event->getRequest());

        // @todo: Support for DNS resolving
        // User account can be expired, deactivated or the UserAgent/IP-Address can be not matching
        if (($user instanceof User)) {
            if (!$user->isNotExpired()) {
                throw AuthenticationException::fromAccountDeactivated();
            }

            if (!$user->isValid($userAgent, $ip)) {
                throw AuthenticationException::fromAccountAccessDeniedBySecurityReason();
            }
        }

        // JWT token can be manually revoked, or just expired
        if ($jwt && !$this->accessTokenAuditRepository->isActiveToken($jwt)) {
            throw AuthenticationException::fromAccessTokenManuallyDeactivatedReason();
        }

        // limit the permissions on the user object
        if ($this->tokenStorage->getToken() instanceof JWTUserToken) {
            $roles = $this->encoder->decode($jwt)['roles'] ?? [];

            /**
             * @var \App\Domain\Common\SharedEntity\User $user
             */
            $user = $this->tokenStorage->getToken()->getUser();

            $this->tokenStorage->setToken(
                new JWTUserToken(
                    $roles,
                    $user->withPermissions($roles),
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
