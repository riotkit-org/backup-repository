<?php declare(strict_types=1);

namespace App\Domain\Common\SharedEntity;

use App\Domain\Authentication\Helper\IdHidingHelper;
use App\Domain\Common\ValueObject\Roles;

class User
{
    public const FIELD_TAGS                      = 'tags';
    public const FIELD_MAX_ALLOWED_FILE_SIZE     = 'maxAllowedFileSize';
    public const FIELD_ALLOWED_IPS               = 'allowedIpAddresses';
    public const FIELD_ALLOWED_UAS               = 'allowedUserAgents';

    // Entity properties
    protected ?string $id    = null;
    protected Roles $roles;

    public const ANONYMOUS_TOKEN_ID = '00000000-0000-0000-0000-000000000000';

    /**
     * Allows to block from writing this entity into the database, in case it was manipulated
     *
     * @var bool $cannotBePersisted
     */
    protected bool $cannotBePersisted = false;

    public function __construct()
    {
        $this->roles = Roles::fromArray([\App\Domain\Roles::ROLE_USER]);
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getCensoredId(): ?string
    {
        if ($this->getId()) {
            return IdHidingHelper::getStrippedOutToken($this->getId());
        }

        return null;
    }

    public function isSameAs(User $token): bool
    {
        return $token->getId() === $this->getId();
    }

    /**
     * @param Roles $roles
     *
     * @return static
     */
    public function setRoles(Roles $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles->getAsList();
    }

    /**
     * In case when eg. a token has fewer roles than user (JWT was generated with limited scope)
     *
     * @param array $roles
     *
     * @return static
     *
     * @throws \App\Domain\Common\Exception\CommonValueException
     */
    public function withRoles(array $roles)
    {
        $clone = clone $this;
        $clone->setRoles(Roles::fromArray($roles));
        $clone->cannotBePersisted = true;

        return $clone;
    }

    public function getRolesAsValueObject(): Roles
    {
        return $this->roles;
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->hasRole($roleName);
    }

    public function getRequestedRolesList(): array
    {
        return $this->roles->getRequestedRolesList();
    }

    /**
     * @return static
     */
    public static function createAnonymousToken()
    {
        $token = new static();
        $token->id    = self::ANONYMOUS_TOKEN_ID;
        $token->roles = Roles::createEmpty();

        return $token;
    }

    public function canBePersisted(): bool
    {
        return $this->id !== self::ANONYMOUS_TOKEN_ID && !$this->cannotBePersisted;
    }
}
