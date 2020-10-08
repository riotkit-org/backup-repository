<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Repository\UserRepository;

class IncomingUserFactory
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string      $userId
     * @param string|null $className
     *
     * @return User
     *
     * @throws AuthenticationException
     */
    public function createFromString(string $userId, string $className = User::class)
    {
        $persistedUser = $this->repository->findUserByUserId($userId, $className);

        if (!$persistedUser) {
            throw AuthenticationException::fromUsersCreationProhibition();
        }

        return $persistedUser;
    }
}
