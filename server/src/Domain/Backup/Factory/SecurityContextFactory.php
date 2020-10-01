<?php declare(strict_types=1);

namespace App\Domain\Backup\Factory;

use App\Domain\Authentication\Entity\User;
use App\Domain\Backup\Security\CollectionManagementContext;
use App\Domain\Backup\Security\VersioningContext;
use App\Domain\Roles;

class SecurityContextFactory
{
    public function createCollectionManagementContext(User $token): CollectionManagementContext
    {
        return new CollectionManagementContext(
            $token->hasRole(Roles::ROLE_COLLECTION_ADD),
            $token->hasRole(Roles::ROLE_COLLECTION_CUSTOM_ID),
            $token->hasRole(Roles::ROLE_COLLECTION_ADD_WITH_INFINITE_LIMITS),
            $token->hasRole(Roles::ROLE_MODIFY_ALLOWED_COLLECTIONS),
            $token->hasRole(Roles::ROLE_COLLECTION_MODIFY_ANY_COLLECTION),
            $token->hasRole(Roles::ROLE_COLLECTION_VIEW_ANY_COLLECTION),
            $token->hasRole(Roles::ROLE_CAN_USE_LISTING_COLLECTION_ENDPOINT),
            $token->hasRole(Roles::ROLE_CAN_MANAGE_TOKENS_IN_ALLOWED_COLLECTIONS),
            $token->hasRole(Roles::ROLE_CAN_DELETE_ALLOWED_COLLECTIONS),
            $token->hasRole(Roles::ROLE_CAN_LIST_TOKENS_IN_COLLECTION),
            $token->hasRole(Roles::ROLE_CANNOT_SEE_FULL_TOKEN_ID),
            $token->hasRole(Roles::ROLE_ADMINISTRATOR),
            $token->getId()
        );
    }

    public function createVersioningContext(User $token): VersioningContext
    {
        return new VersioningContext(
            $token->hasRole(Roles::ROLE_COLLECTION_MODIFY_ANY_COLLECTION),
            $token->hasRole(Roles::ROLE_CAN_UPLOAD_TO_ALLOWED_COLLECTIONS),
            $token->hasRole(Roles::ROLE_LIST_VERSIONS_FOR_ALLOWED_COLLECTIONS),
            $token->hasRole(Roles::ROLE_DELETE_VERSIONS_IN_ALLOWED_COLLECTIONS),
            $token->getId()
        );
    }

    public function createShellContext(): CollectionManagementContext
    {
        return new CollectionManagementContext(
            true, true,
            true, true,
            true, true,
            true, true,
            true, true, false, true, null
        );
    }
}
