<?php declare(strict_types=1);

namespace App\Domain\Authentication\Service\Security;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use App\Domain\Authentication\Exception\RepeatableJWTException;
use App\Domain\Authentication\Exception\UserNotFoundException;
use App\Domain\Authentication\Factory\JWTFactory;
use App\Domain\Authentication\Manager\UserManager;
use App\Domain\Authentication\Repository\AccessTokenAuditRepository;
use App\Domain\Authentication\Repository\UserRepository;

/**
 * Records each ACCESS GRANT using JSON Web Tokens (JWT)
 * =====================================================
 *
 * Recording gives us possibilities to:
 *   - Audit the system
 *   - Revoke tokens that fell into wrong hands
 *   - Give user possibility to see it's devices/clients logged in
 */
class AccessTokenAuditRecorder
{
    private JWTFactory $factory;
    private UserRepository $userRepository;
    private AccessTokenAuditRepository $repository;
    private UserManager $manager;

    public function __construct(JWTFactory $factory, UserRepository $userRepository,
                                AccessTokenAuditRepository $repository, UserManager $manager)
    {
        $this->factory        = $factory;
        $this->userRepository = $userRepository;
        $this->repository     = $repository;
        $this->manager        = $manager;
    }

    /**
     * @param string $jwtToken
     * @param string $description
     *
     * @throws UserNotFoundException
     */
    public function record(string $jwtToken, string $description = ''): void
    {
        $payload = $this->factory->createArrayFromToken($jwtToken);
        $email       = $payload['email'];
        $permissions = $payload['roles'];
        $expiration  = $payload['exp'];

        $user = $this->userRepository->findOneByEmail($email);

        if (!$user || !$user->isNotExpired() || !$user->isActive()) {
            throw UserNotFoundException::fromNoLongerFoundCause();
        }

        try {
            $accessToken = AccessTokenAuditEntry::createFrom($jwtToken, $user, $permissions, $expiration, $description);

            $this->repository->persist($accessToken);
            $this->repository->flush();

        } catch (RepeatableJWTException $exception) {
            // pass: the JWT's are repeatable in short period of time
        }
    }
}
