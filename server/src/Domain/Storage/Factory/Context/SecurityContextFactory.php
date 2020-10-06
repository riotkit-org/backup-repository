<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory\Context;

use App\Domain\Authentication\Entity\User;
use App\Domain\Roles;
use App\Domain\Storage\Form\FilesListingForm;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Domain\Storage\Security\UploadSecurityContext;

class SecurityContextFactory
{
    public function createUploadContextFromToken(User $token): UploadSecurityContext
    {
        return new UploadSecurityContext(
            $token->getTags(),
            $token->hasRole(Roles::ROLE_UPLOAD),
            $token->hasRole(Roles::ROLE_ALLOW_OVERWRITE_FILES),
            $token->getMaxAllowedFileSize(),
            $token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_TOKEN_TAGS),
            $token->hasRole(Roles::ROLE_ADMINISTRATOR),
            $token->hasRole(Roles::ROLE_UPLOAD_ONLY_ONCE_SUCCESSFUL),
            $token
        );
    }

    public function createViewingContextFromTokenAndForm(User $token, ViewFileForm $form, bool $isFileAlreadyValidated = false): ReadSecurityContext
    {
        return new ReadSecurityContext(
            $token->hasRole(Roles::ROLE_BROWSE_ALL_FILES),
            $token->hasRole(Roles::ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG),
            $token->hasRole(Roles::ROLE_ACCESS_LISTING_ENDPOINT),
            $token->getTags(),
            $token->hasRole(Roles::ROLE_CAN_SEE_EXTRA_ADMIN_METADATA),
            $token,
            $isFileAlreadyValidated
        );
    }

    public function createListingContextFromTokenAndForm(User $token, FilesListingForm $form, bool $isFileAlreadyValidated = false): ReadSecurityContext
    {
        return new ReadSecurityContext(
            $token->hasRole(Roles::ROLE_BROWSE_ALL_FILES),
            $token->hasRole(Roles::ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG),
            $token->hasRole(Roles::ROLE_ACCESS_LISTING_ENDPOINT),
            $token->getTags(),
            $token->hasRole(Roles::ROLE_CAN_SEE_EXTRA_ADMIN_METADATA),
            $token,
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
