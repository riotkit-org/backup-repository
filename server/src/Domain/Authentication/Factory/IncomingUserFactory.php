<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Common\Exception\CommonValueException;

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
     * @param array|null  $overwritePermissions In case when eg. a token has fewer roles than user (JWT was generated with limited scope)
     *
     * @return User
     *
     * @throws AuthenticationException
     * @throws CommonValueException
     */
    public function createFromString(string $userId, ?string $className = User::class,
                                     ?array $overwritePermissions = null): \App\Domain\Common\SharedEntity\User
    {
        /**
         * @var \App\Domain\Common\SharedEntity\User $persistedUser
         */
        $persistedUser = $this->repository->findUserByUserId($userId, $className);

        if (!$persistedUser) {
            throw AuthenticationException::fromUsersCreationProhibition();
        }

        if ($overwritePermissions) {
            $persistedUser = $persistedUser->withPermissions($overwritePermissions);
        }

        return $persistedUser;
    }
}
