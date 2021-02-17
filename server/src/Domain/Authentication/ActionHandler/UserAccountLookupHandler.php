<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Authentication\Response\UserCRUDResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class UserAccountLookupHandler
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $uidToLookup
     * @param AuthenticationManagementContext $context
     *
     * @return null|UserCRUDResponse
     *
     * @throws AuthenticationException
     */
    public function handle(string $uidToLookup, AuthenticationManagementContext $context): ?UserCRUDResponse
    {
        $user = $this->repository->findUserByUserId($uidToLookup);

        $this->assertHasRights($context);

        if (!$user instanceof User) {
            return null;
        }

        return UserCRUDResponse::createFoundResponse($user);
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context): void
    {
        if (!$context->canLookupAnyUserAccount()) {
            throw AuthenticationException::fromNoPermissionToLookupUser();
        }
    }
}
