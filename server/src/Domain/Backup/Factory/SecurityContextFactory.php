<?php declare(strict_types=1);

namespace App\Domain\Backup\Factory;

use App\Domain\Authentication\ValueObject\Roles as UserRoles;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Repository\UserAccessRepository;
use App\Domain\Backup\Security\CollectionManagementContext;
use App\Domain\Backup\Security\VersioningContext;
use App\Domain\Roles;

class SecurityContextFactory
{
    private UserAccessRepository $accessRepository;

    public function __construct(UserAccessRepository $accessRepository)
    {
        $this->accessRepository = $accessRepository;
    }

    public function createCollectionManagementContext(User $user, ?BackupCollection $collection): CollectionManagementContext
    {
        $roles = $this->getRolesIncludingContextOfACollection($collection, $user);

        return new CollectionManagementContext(
            $roles->hasRole(Roles::ROLE_COLLECTION_ADD),
            $roles->hasRole(Roles::ROLE_COLLECTION_CUSTOM_ID),
            $roles->hasRole(Roles::ROLE_COLLECTION_ADD_WITH_INFINITE_LIMITS),
            $roles->hasRole(Roles::ROLE_MODIFY_ALLOWED_COLLECTIONS),
            $roles->hasRole(Roles::ROLE_COLLECTION_MODIFY_ANY_COLLECTION),
            $roles->hasRole(Roles::ROLE_COLLECTION_VIEW_ANY_COLLECTION),
            $roles->hasRole(Roles::ROLE_CAN_USE_LISTING_COLLECTION_ENDPOINT),
            $roles->hasRole(Roles::ROLE_CAN_MANAGE_TOKENS_IN_ALLOWED_COLLECTIONS),
            $roles->hasRole(Roles::ROLE_CAN_DELETE_ALLOWED_COLLECTIONS),
            $roles->hasRole(Roles::ROLE_CAN_LIST_TOKENS_IN_COLLECTION),
            $roles->hasRole(Roles::ROLE_CANNOT_SEE_FULL_TOKEN_ID),
            $roles->hasRole(Roles::ROLE_ADMINISTRATOR),
            $user->getId(),
            $user
        );
    }

    public function createVersioningContext(User $user, BackupCollection $collection): VersioningContext
    {
        $roles = $this->getRolesIncludingContextOfACollection($collection, $user);

        return new VersioningContext(
            $roles->hasRole(Roles::ROLE_COLLECTION_MODIFY_ANY_COLLECTION),
            $roles->hasRole(Roles::ROLE_CAN_UPLOAD_TO_ALLOWED_COLLECTIONS),
            $roles->hasRole(Roles::ROLE_LIST_VERSIONS_FOR_ALLOWED_COLLECTIONS),
            $roles->hasRole(Roles::ROLE_DELETE_VERSIONS_IN_ALLOWED_COLLECTIONS),
            $user->getId()
        );
    }

    public function createShellContext(): CollectionManagementContext
    {
        return new CollectionManagementContext(
            true, true,
            true, true,
            true, true,
            true, true,
            true, true, false, true, null, null
        );
    }

    private function getRolesIncludingContextOfACollection(?BackupCollection $collection, User $user): UserRoles
    {
        $access = $collection ? $this->accessRepository->findForCollectionAndUser($collection, $user) : null;
        $roles  = UserRoles::createEmpty()->mergeWith($user->getRolesAsValueObject());

        if ($access) {
            $roles = $roles->mergeWith($access->getRoles());
        }

        return $roles;
    }
}
