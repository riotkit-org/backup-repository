<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Manager\UserManager;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Authentication\Response\UserCRUDResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class UserAccountDeleteHandler
{
    private UserRepository $repository;
    private UserManager $manager;

    public function __construct(UserRepository $repository, UserManager $manager)
    {
        $this->repository = $repository;
        $this->manager    = $manager;
    }

    /**
     * @param string $userId
     * @param AuthenticationManagementContext $context
     *
     * @return null|UserCRUDResponse
     *
     * @throws AuthenticationException
     */
    public function handle(string $userId, AuthenticationManagementContext $context): UserCRUDResponse
    {
        $user = $this->repository->findUserByUserId($userId);

        if (!$user instanceof User) {
            return UserCRUDResponse::createNotFoundResponse();
        }

        $this->assertHasRights($context, $user);

        $this->manager->revokeAccessForUser($user);
        $this->manager->flushAll();

        return UserCRUDResponse::createDeletedResponse($user);
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param User                           $user
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context, User $user): void
    {
        if (!$context->canRevokeAccess($user)) {
            throw AuthenticationException::fromDeletionProhibited();
        }
    }
}
