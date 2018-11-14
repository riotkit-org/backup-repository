<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory\Context;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Roles;
use App\Domain\Storage\Provider\MimeTypeProvider;
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

    public function createFromToken(Token $token): UploadSecurityContext
    {
        return new UploadSecurityContext(
            $this->createListOfMimeTypes($token),
            $token->getTags(),
            $token->hasRole(Roles::ROLE_UPLOAD)
                || $token->hasRole(Roles::ROLE_UPLOAD_DOCS)
                || $token->hasRole(Roles::ROLE_UPLOAD_IMAGES)
                || $token->hasRole(Roles::ROLE_UPLOAD_BACKUP),
            $token->hasRole(Roles::ROLE_ALLOW_OVERWRITE_FILES)
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
