<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory\Context;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Roles;
use App\Domain\Storage\Form\DeleteFileForm;
use App\Domain\Storage\Form\FilesListingForm;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Provider\MimeTypeProvider;
use App\Domain\Storage\Security\ManagementSecurityContext;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Domain\Storage\Security\UploadSecurityContext;

class SecurityContextFactory
{
    public function createUploadContextFromToken(Token $token): UploadSecurityContext
    {
        return new UploadSecurityContext(
            $token->getTags(),
            $token->hasRole(Roles::ROLE_UPLOAD),
            $token->hasRole(Roles::ROLE_ALLOW_OVERWRITE_FILES),
            $token->getMaxAllowedFileSize(),
            $token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_TOKEN_TAGS),
            $token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_NO_PASSWORD),
            $token->hasRole(Roles::ROLE_ADMINISTRATOR),
            $token->hasRole(Roles::ROLE_UPLOAD_ONLY_ONCE_SUCCESSFUL),
            $token
        );
    }

    public function createViewingContextFromTokenAndForm(Token $token, ViewFileForm $form, bool $isFileAlreadyValidated = false): ReadSecurityContext
    {
        return new ReadSecurityContext(
            $token->hasRole(Roles::ROLE_VIEW_ALL_PROTECTED_FILES),
            $token->hasRole(Roles::ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG),
            $token->hasRole(Roles::ROLE_ACCESS_LISTING_ENDPOINT),
            $form->password ?? '',
            $token->getTags(),
            $token->hasRole(Roles::ROLE_CAN_SEE_EXTRA_ADMIN_METADATA),
            $token,
            $isFileAlreadyValidated
        );
    }

    public function createListingContextFromTokenAndForm(Token $token, FilesListingForm $form, bool $isFileAlreadyValidated = false): ReadSecurityContext
    {
        return new ReadSecurityContext(
            $token->hasRole(Roles::ROLE_VIEW_ALL_PROTECTED_FILES),
            $token->hasRole(Roles::ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG),
            $token->hasRole(Roles::ROLE_ACCESS_LISTING_ENDPOINT),
            $form->password ?? '',
            $token->getTags(),
            $token->hasRole(Roles::ROLE_CAN_SEE_EXTRA_ADMIN_METADATA),
            $token,
            $isFileAlreadyValidated
        );
    }

    public function createReadContextInShell(): ReadSecurityContext
    {
        return new ReadSecurityContext(
            true, true, true, '', [], true,
            Token::createAnonymousToken(), true
        );
    }

    public function createDeleteContextFromTokenAndForm(Token $token, DeleteFileForm $form): ManagementSecurityContext
    {
        return new ManagementSecurityContext(
            $token->hasRole(Roles::ROLE_DELETE_ALL_FILES),
            $form->password ?? ''
        );
    }
}
