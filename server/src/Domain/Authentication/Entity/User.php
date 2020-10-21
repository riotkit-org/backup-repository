<?php declare(strict_types=1);

namespace App\Domain\Authentication\Entity;

use App\Domain\Authentication\Configuration\PasswordHashingConfiguration;
use App\Domain\Authentication\ValueObject\About;
use App\Domain\Authentication\ValueObject\Email;
use App\Domain\Authentication\ValueObject\ExpirationDate;
use App\Domain\Authentication\ValueObject\Organization;
use App\Domain\Authentication\ValueObject\Password;
use App\Domain\Authentication\ValueObject\Roles;
use App\Domain\Common\Exception\DomainAssertionFailure;
use App\Domain\Common\SharedEntity\EntityValidationTrait;
use DateTimeImmutable;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends \App\Domain\Common\SharedEntity\User implements \JsonSerializable, UserInterface
{
    use EntityValidationTrait;

    /**
     * @SWG\Property(type="string", maxLength=32, example="2020-05-01 08:00:00")
     *
     * @var DateTimeImmutable $creationDate
     */
    protected $creationDate;

    /**
     * @SWG\Property(type="string", maxLength=32, example="2020-05-01 08:00:00")
     *
     * @var null|ExpirationDate
     */
    protected ?ExpirationDate $expirationDate;

    /**
     * @SWG\Property(
     *     type="array",
     *     @SWG\Items(
     *         type="object"
     *     )
     * )
     *
     * @var string[]|array[]
     */
    protected $data = [];

    protected string        $salt;
    protected Password      $passphrase;
    protected Email         $email;
    protected Organization  $organization;
    protected About         $about;

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
        $this->expirationDate = ExpirationDate::fromString('now', 'now');
        $this->creationDate   = new DateTimeImmutable();
        $this->active         = true;
        $this->salt           = substr(base64_encode(random_bytes(32)), 0, 32);
        $this->passphrase     = new Password();

        parent::__construct();
    }

    /**
     * @param array $roles
     * @param ?string $expirationTime
     * @param array $details
     * @param ?string $email
     * @param ?string $password
     * @param ?string $organizationName
     * @param ?string $about
     * @param PasswordHashingConfiguration $configuration
     * @param string $defaultExpirationTime
     *
     * @return static
     *
     * @throws DomainAssertionFailure
     */
    public static function createFrom(array $roles, ?string $expirationTime, array $details,
                                      ?string $email, ?string $password, ?string $organizationName, ?string $about,
                                      PasswordHashingConfiguration $configuration, string $defaultExpirationTime)
    {
        $new = new static();

        static::withValidationErrorAggregation([
            static function () use ($new, $roles) {
                $new->setRoles(Roles::fromArray($roles));
            },
            static function () use ($new, $expirationTime, $defaultExpirationTime) {
                $new->setExpirationDate(ExpirationDate::fromString($expirationTime, $defaultExpirationTime));
            },
            static function () use ($new, $details) {
                $new->setData($details);
            },
            static function () use ($new, $email) {
                $new->setEmail(Email::fromString((string) $email));
            },
            static function () use ($new, $password, $configuration) {
                $new->setPassphrase(Password::fromString((string) $password, $new->salt, $configuration));
            },
            static function () use ($new, $organizationName) {
                $new->setOrganization(Organization::fromString((string) $organizationName));
            },
            static function () use ($new, $about) {
                $new->setAbout(About::fromString((string) $about));
            }
        ]);

        return $new;
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
        return $this->expirationDate->getValue();
    }

    public function setCreationDate(DateTimeImmutable $creationDate): User
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    public function setExpirationDate(ExpirationDate $expirationDate): User
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    private function setEmail(Email $email): User
    {
        $this->email = $email;
        return $this;
    }

    private function setPassphrase(Password $password): User
    {
        $this->passphrase = $password;
        return $this;
    }

    private function setOrganization(Organization $organization): User
    {
        $this->organization = $organization;
        return $this;
    }

    private function setAbout(About $about): User
    {
        $this->about = $about;
        return $this;
    }

    public function activate(): User
    {
        $this->active = true;
        return $this;
    }

    public function deactivate(): User
    {
        $this->active = false;
        return $this;
    }

    public function setData(array $data): User
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

    public function getMaxAllowedFileSize(): int
    {
        return isset($this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE]) && \is_int($this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE])
            ? $this->data[self::FIELD_MAX_ALLOWED_FILE_SIZE] : 0;
    }

    public function isValid(string $userAgent, string $ipAddress): bool
    {
        if (\App\Domain\Roles::isTestToken($this->getId())) {
            return true;
        }

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

    public function jsonSerialize(bool $censorId = false)
    {
        return [
            'id'           => $censorId ? $this->getCensoredId() : $this->getId(),
            'email'        => $this->email->getValue(),
            'active'       => $this->active,
            'expired'      => !$this->isNotExpired(),
            'expires'      => $this->getExpirationDate()->format('Y-m-d H:i:s'),
            'data'         => $this->data,
            'roles'        => $this->getRoles(),
            'idIsCensored' => $censorId
        ];
    }

    public function getPassword()
    {
        return $this->passphrase->getValue();
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
