<?php declare(strict_types=1);

namespace App\Domain\Authentication\Entity;

use DateTimeImmutable;
use Swagger\Annotations as SWG;

class Token extends \App\Domain\Common\SharedEntity\Token implements \JsonSerializable
{
    /**
     * @SWG\Property(type="string", maxLength=32, example="2020-05-01 08:00:00")
     *
     * @var DateTimeImmutable $creationDate
     */
    protected $creationDate;

    /**
     * @SWG\Property(type="string", maxLength=32, example="2020-05-01 08:00:00")
     *
     * @var DateTimeImmutable
     */
    protected $expirationDate;

    /**
     * @SWG\Property(
     *     type="array",
     *     @SWG\Items(
     *         type="object"
     *     )
     * )
     *
     * @var string[]
     */
    protected $data = [];

    /**
     * @SWG\Property(type="boolean")
     *
     * @var bool
     */
    protected bool $active;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->expirationDate = new DateTimeImmutable();
        $this->creationDate = new DateTimeImmutable();
        $this->active = true;
    }

    public function setId(string $id): Token
    {
        $this->id = $id;
        return $this;
    }

    public function isNotExpired(DateTimeImmutable $currentDate = null): bool
    {
        if (!$currentDate instanceof DateTimeImmutable) {
            $currentDate = new DateTimeImmutable();
        }

        return $this->getExpirationDate()->getTimestamp() >= $currentDate->getTimestamp();
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function getExpirationDate(): DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setCreationDate(DateTimeImmutable $creationDate): Token
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    public function setExpirationDate(DateTimeImmutable $expirationDate): Token
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
        return isset($this->data[self::FIELD_TAGS]) && \is_array($this->data[self::FIELD_TAGS])
            ? $this->data[self::FIELD_TAGS] : [];
    }

    /**
     * @return string[]
     */
    public function getAllowedMimeTypes(): array
    {
        return isset($this->data[self::FIELD_ALLOWED_MIME_TYPES]) && \is_array($this->data[self::FIELD_ALLOWED_MIME_TYPES])
            ? $this->data[self::FIELD_ALLOWED_MIME_TYPES] : [];
    }

    public function getMaxAllowedFileSize(): int
    {
        return isset($this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE]) && \is_int($this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE])
            ? $this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE] : 0;
    }

    public function isValid(string $userAgent, string $ipAddress): bool
    {
        if (!$this->isNotExpired(new DateTimeImmutable())) {
            return false;
        }

        return $this->canBeUsedByIpAddress($ipAddress)
            && $this->canBeUsedWithUserAgent($userAgent);
    }

    public function isAnonymous(): bool
    {
        return $this->id === null;
    }

    public function getAllowedUserAgents(): array
    {
        return $this->data[self::FIELD_ALLOWED_UAS] ?? [];
    }

    public function getAllowedIpAddresses(): array
    {
        return $this->data[self::FIELD_ALLOWED_IPS] ?? [];
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

    public function jsonSerialize()
    {
        return [
            'id'      => $this->getId(),
            'active'  => $this->active,
            'expired' => !$this->isNotExpired(),
            'expires' => $this->getExpirationDate()->format('Y-m-d H:i:s'),
            'data'    => $this->data,
            'roles'   => $this->getRoles()
        ];
    }
}
