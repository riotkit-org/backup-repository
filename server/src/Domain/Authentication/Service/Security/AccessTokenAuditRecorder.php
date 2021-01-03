<?php declare(strict_types=1);

namespace App\Domain\Authentication\Service\Security;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use App\Domain\Authentication\Exception\UserNotFoundException;
use App\Domain\Authentication\Factory\JWTFactory;
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

    public function __construct(JWTFactory $factory, UserRepository $userRepository, AccessTokenAuditRepository $repository)
    {
        $this->factory = $factory;
        $this->userRepository = $userRepository;
        $this->repository = $repository;
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

        if (!$user) {
            throw UserNotFoundException::fromNoLongerFoundCause();
        }

        $this->repository->persist(AccessTokenAuditEntry::createFrom($jwtToken, $user, $permissions, $expiration, $description));
        $this->repository->flush();
    }
}
