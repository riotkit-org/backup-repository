<?php declare(strict_types=1);

namespace App\Domain\Common\SharedEntity;

use App\Domain\Common\ValueObject\Roles;
use App\Domain\Roles as RolesConst;

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

    /**
     * @var string[]|array[]
     */
    protected $data = [];

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

    /**
     * Checks if regardless of domain the user is the same
     *
     * @param User $user
     *
     * @return bool
     */
    public function isSameAs(User $user): bool
    {
        return $this->getId() === $user->getId();
    }

    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
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

    public function isAdministrator(): bool
    {
        return $this->hasRole(RolesConst::PERMISSION_ADMINISTRATOR);
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

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return isset($this->data[self::FIELD_TAGS]) && \is_array($this->data[self::FIELD_TAGS])
            ? $this->data[self::FIELD_TAGS] : [];
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMaxAllowedFileSize(): int
    {
        return isset($this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE]) && \is_int($this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE])
            ? $this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE] : 0;
    }
}
