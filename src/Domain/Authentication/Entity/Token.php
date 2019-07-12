<?php declare(strict_types=1);

namespace App\Domain\Authentication\Entity;

use App\Domain\Roles;

class Token
{
    public const REQUIRED_FIELDS = [
        'roles'          => 'array',
        'expirationDate' => 'string',
        'data'           => 'array'
    ];

    /**
     * @var string $id
     */
    private $id;

    /**
     * @var array $roles
     */
    private $roles = [];

    /**
     * @var bool
     */
    private $alreadyGrantedAdminAccess = false;

    /**
     * @var \DateTimeImmutable $creationDate
     */
    private $creationDate;

    /**
     * @var \DateTimeImmutable
     */
    private $expirationDate;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $active;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->expirationDate = new \DateTimeImmutable();
        $this->creationDate   = new \DateTimeImmutable();
        $this->active         = true;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Token
    {
        $this->id = $id;
        return $this;
    }

    public function getRoles(): array
    {
        if (!$this->alreadyGrantedAdminAccess && \in_array(Roles::ROLE_ADMINISTRATOR, $this->roles, true)) {
            $this->roles = \array_merge($this->roles, Roles::GRANTS_LIST);
            $this->alreadyGrantedAdminAccess = true;
        }

        return $this->roles;
    }

    public function hasRole(string $roleName): bool
    {
        return \in_array($roleName, $this->getRoles(), true);
    }

    public function isNotExpired(\DateTimeImmutable $currentDate = null): bool
    {
        if (!$currentDate instanceof \DateTimeImmutable) {
            $currentDate = new \DateTimeImmutable();
        }

        return $this->getExpirationDate()->getTimestamp() >= $currentDate->getTimestamp();
    }

    public function getCreationDate(): \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function getExpirationDate(): \DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setRoles(array $roles): Token
    {
        $this->roles = $roles;
        return $this;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): Token
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    public function setExpirationDate(\DateTimeImmutable $expirationDate): Token
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    public function activate(): Token
    {
        $this->active = true;
        return $this;
    }

    public function deactivate(): Token
    {
        $this->active = false;
        return $this;
    }

    public function setData(array $data): Token
    {
        $this->data = $data;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return isset($this->data['tags']) && \is_array($this->data['tags']) ? $this->data['tags'] : [];
    }

    /**
     * @return string[]
     */
    public function getAllowedMimeTypes(): array
    {
        return isset($this->data['allowedMimeTypes']) && \is_array($this->data['allowedMimeTypes']) ? $this->data['allowedMimeTypes'] : [];
    }

    public function getMaxAllowedFileSize(): int
    {
        return isset($this->data['maxAllowedFileSize']) && \is_int($this->data['maxAllowedFileSize']) ? $this->data['maxAllowedFileSize'] : 0;
    }

    public function isValid(string $userAgent, string $ipAddress): bool
    {
        if (!$this->isNotExpired(new \DateTimeImmutable())) {
            return false;
        }

        return $this->canBeUsedByIpAddress($ipAddress)
            && $this->canBeUsedWithUserAgent($userAgent);
    }

    public function getAllowedUserAgents(): array
    {
        return $this->data['allowedUserAgents'] ?? [];
    }

    public function getAllowedIpAddresses(): array
    {
        return $this->data['allowedIpAddresses'] ?? [];
    }

    private function canBeUsedByIpAddress(string $address): bool
    {
        if (!$this->getAllowedIpAddresses()) {
            return true;
        }

        return \in_array($address, $this->getAllowedIpAddresses(), true);
    }

    private function canBeUsedWithUserAgent(string $userAgent): bool
    {
        if (!$this->getAllowedUserAgents()) {
            return true;
        }

        return \in_array($userAgent, $this->getAllowedUserAgents(), true);
    }
}
