<?php declare(strict_types=1);

namespace App\Domain\Common\SharedEntity;

use App\Domain\Roles;

class Token
{
    public const FIELD_TAGS                   = 'tags';
    public const FIELD_ALLOWED_MIME_TYPES     = 'allowedMimeTypes';
    public const FIELD_MAX_ALLOWED_FILE_SIZE  = 'maxAllowedFileSize';
    public const FIELD_ALLOWED_IPS            = 'allowedIpAddresses';
    public const FIELD_ALLOWED_UAS            = 'allowedUserAgents';
    public const FIELD_SECURE_COPY_ENC_METHOD = 'secureCopyEncryptionMethod';
    public const FIELD_SECURE_COPY_ENC_KEY    = 'secureCopyEncryptionKey';

    /**
     * @var string $id
     */
    protected $id;

    protected array $roles = [];
    private bool $alreadyGrantedAdminAccess = false;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function isSameAs(Token $token): bool
    {
        return $token->getId() === $this->getId();
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_values(array_unique($roles));

        return $this;
    }

    public function getRoles(): array
    {
        if (!$this->alreadyGrantedAdminAccess && \in_array(Roles::ROLE_ADMINISTRATOR, $this->roles, true)) {
            $this->setRoles(\array_merge($this->roles, Roles::GRANTS_LIST));
            $this->alreadyGrantedAdminAccess = true;
        }

        return $this->roles;
    }

    public function hasRole(string $roleName): bool
    {
        return \in_array($roleName, $this->getRoles(), true);
    }
}
