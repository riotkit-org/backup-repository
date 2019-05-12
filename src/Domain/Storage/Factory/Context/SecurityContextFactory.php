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
    /**
     * @var MimeTypeProvider
     */
    private $mimeProvider;

    public function __construct(MimeTypeProvider $mimeTypeProvider)
    {
        $this->mimeProvider = $mimeTypeProvider;
    }

    public function createUploadContextFromToken(Token $token): UploadSecurityContext
    {
        return new UploadSecurityContext(
            $this->createListOfMimeTypes($token),
            $token->getTags(),
            $token->hasRole(Roles::ROLE_UPLOAD)
                || $token->hasRole(Roles::ROLE_UPLOAD_DOCS)
                || $token->hasRole(Roles::ROLE_UPLOAD_IMAGES)
                || $token->hasRole(Roles::ROLE_UPLOAD_BACKUP)
                || $token->hasRole(Roles::ROLE_UPLOAD_VIDEOS),
            $token->hasRole(Roles::ROLE_ALLOW_OVERWRITE_FILES),
            $token->getMaxAllowedFileSize(),
            $token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_TOKEN_TAGS),
            $token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_NO_PASSWORD),
            $token->hasRole(Roles::ROLE_ADMINISTRATOR)
        );
    }

    public function createViewingContextFromTokenAndForm(Token $token, ViewFileForm $form): ReadSecurityContext
    {
        return new ReadSecurityContext(
            $token->hasRole(Roles::ROLE_VIEW_ALL_PROTECTED_FILES),
            $token->hasRole(Roles::ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG),
            $token->hasRole(Roles::ROLE_ACCESS_LISTING_ENDPOINT),
            $form->password ?? '',
            $token->getTags()
        );
    }

    public function createListingContextFromTokenAndForm(Token $token, FilesListingForm $form): ReadSecurityContext
    {
        return new ReadSecurityContext(
            $token->hasRole(Roles::ROLE_VIEW_ALL_PROTECTED_FILES),
            $token->hasRole(Roles::ROLE_BROWSE_LIST_OF_FILES_BY_ANY_TAG),
            $token->hasRole(Roles::ROLE_ACCESS_LISTING_ENDPOINT),
            $form->password ?? '',
            $token->getTags()
        );
    }

    public function createDeleteContextFromTokenAndForm(Token $token, DeleteFileForm $form): ManagementSecurityContext
    {
        return new ManagementSecurityContext(
            $token->hasRole(Roles::ROLE_DELETE_ALL_FILES),
            $form->password ?? ''
        );
    }

    private function createListOfMimeTypes(Token $token): array
    {
        $mimes = $token->getAllowedMimeTypes();
        $roleBasedMimeTypes = [];

        foreach ($token->getRoles() as $role) {
            $roleBasedMimeTypes[] = $this->mimeProvider->getMimesForRole($role);
        }

        return \array_merge($mimes, ...$roleBasedMimeTypes);
    }
}
