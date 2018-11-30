<?php declare(strict_types=1);

namespace App\Domain\Authentication\Entity;

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
}
