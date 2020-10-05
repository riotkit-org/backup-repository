<?php declare(strict_types=1);

namespace App\Domain\Authentication\Manager;

use App\Domain\Authentication\Configuration\PasswordHashingConfiguration;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\InvalidUserIdException;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Authentication\Service\UuidValidator;
use App\Domain\Common\Exception\DomainAssertionFailure;

/**
 * @todo: Rewrite into commands
 */
class UserManager
{
    private UserRepository $repository;
    private UuidValidator $uuidValidator;
    private PasswordHashingConfiguration $hashingConfiguration;
    private string $defaultExpirationTime;

    public function __construct(UserRepository $repository, UuidValidator $uuidValidator, PasswordHashingConfiguration $hashingConfiguration)
    {
        $this->repository    = $repository;
        $this->uuidValidator = $uuidValidator;
        $this->hashingConfiguration = $hashingConfiguration;
        $this->defaultExpirationTime = '+2 hours'; // @todo parametrize
    }

    /**
     * @param array  $roles
     * @param ?string $expirationTime
     * @param array  $details
     * @param ?string $email
     * @param ?string $password
     * @param ?string $organizationName
     * @param ?string $about
     * @param string|null $customId
     *
     * @return User
     *
     * @throws InvalidUserIdException
     * @throws DomainAssertionFailure
     */
    public function createUser(array $roles, ?string $expirationTime, array $details, ?string $email,
                               ?string $password, ?string $organizationName, ?string $about,
                               ?string $customId = null): User
    {
        if ($customId) {
            if (!$this->uuidValidator->isValid($customId)) {
                throw new InvalidUserIdException();
            }
        }

        $user = User::createFrom(
            $roles,
            $expirationTime,
            $details,
            $email,
            $password,
            $organizationName,
            $about,
            $this->hashingConfiguration,
            $this->defaultExpirationTime
        );

        $this->repository->persist($user);

        if ($customId) {
            $user->setId($customId);
            $this->repository->persist($user);
        }

        return $user;
    }

    public function revokeAccessForUser(User $user): void
    {
        $this->repository->remove($user);
    }

    public function flushAll(): void
    {
        $this->repository->flush();
    }
}
