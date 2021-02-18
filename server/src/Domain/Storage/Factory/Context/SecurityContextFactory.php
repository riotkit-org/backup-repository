<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory\Context;

use App\Domain\Common\SharedEntity\User;
use App\Domain\Roles;
use App\Domain\Storage\Form\FilesListingForm;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Domain\Storage\Security\UploadSecurityContext;

class SecurityContextFactory
{
    public function createUploadContextFromToken(User $user): UploadSecurityContext
    {
        return new UploadSecurityContext(
            $user->getTags(),
            $user->hasRole(Roles::PERMISSION_UPLOAD),
            $user->hasRole(Roles::PERMISSION_ALLOW_OVERWRITE_FILES),
            $user->getMaxAllowedFileSize(),
            $user->hasRole(Roles::PERMISSION_UPLOAD_ENFORCE_USER_TAGS),
            $user->hasRole(Roles::PERMISSION_ADMINISTRATOR),
            $user->hasRole(Roles::PERMISSION_UPLOAD_ONLY_ONCE_SUCCESSFUL),
            $user
        );
    }

    public function createViewingContextFromTokenAndForm(User $token, ViewFileForm $form, bool $isFileAlreadyValidated = false): ReadSecurityContext
    {
        return new ReadSecurityContext(
            $token->hasRole(Roles::PERMISSION_BROWSE_ALL_FILES),
            $token->hasRole(Roles::PERMISSION_BROWSE_LIST_OF_FILES_BY_ANY_TAG),
            $token->hasRole(Roles::PERMISSION_ACCESS_LISTING_ENDPOINT),
            $token->getTags(),
            $token->hasRole(Roles::PERMISSION_CAN_SEE_EXTRA_ADMIN_METADATA),
            $token,
            $isFileAlreadyValidated
        );
    }

    public function createListingContextFromTokenAndForm(User $user, FilesListingForm $form, bool $isFileAlreadyValidated = false): ReadSecurityContext
    {
        return new ReadSecurityContext(
            $user->hasRole(Roles::PERMISSION_BROWSE_ALL_FILES),
            $user->hasRole(Roles::PERMISSION_BROWSE_LIST_OF_FILES_BY_ANY_TAG),
            $user->hasRole(Roles::PERMISSION_ACCESS_LISTING_ENDPOINT),
            $user->getTags(),
            $user->hasRole(Roles::PERMISSION_CAN_SEE_EXTRA_ADMIN_METADATA),
            $user,
            $isFileAlreadyValidated
        );
    }

    public function createReadContextInShell(): ReadSecurityContext
    {
        return new ReadSecurityContext(
            true, true, true, [], true,
            User::createAnonymousToken(), true
        );
    }
}
