<?php declare(strict_types=1);

namespace App\Domain\Authentication\Entity;

use App\Domain\Authentication\Service\Security\HashEncoder;
use App\Domain\Authentication\ValueObject\Permissions;

/**
 * Access Token Audit Entry represents each token creation event
 * -------------------------------------------------------------
 *   - Each time user is logging
 *   - Each time user is generating an API token
 */
class AccessTokenAuditEntry implements \JsonSerializable
{
    private string $id;

    private \DateTimeImmutable $date;

    private \DateTimeImmutable $expiration;

    private bool $active;

    private User $user;

    private string $tokenHash;

    private string $tokenShortcut;

    private Permissions $permissions;

    private string $description;

    public static function createFrom(string $token, User $user, array $permissions, int $expiration, string $description)
    {
        // @todo: Use value objects

        $auditEntry = new static();
        $auditEntry->date          = new \DateTimeImmutable();
        $auditEntry->expiration    = (new \DateTimeImmutable())->setTimestamp($expiration);
        $auditEntry->user          = $user;
        $auditEntry->tokenHash     = HashEncoder::encode($token);
        $auditEntry->tokenShortcut = substr($token, 0, 16) . '...' . substr($token, -16);
        $auditEntry->permissions   = Permissions::fromArray($permissions);
        $auditEntry->active        = true;
        $auditEntry->description   = $description;

        return $auditEntry;
    }

    public function isStillValid(): bool
    {
        if (!$this->active) {
            return false;
        }

        return $this->expiration >= new \DateTimeImmutable();
    }

    public function jsonSerialize(): array
    {
        return [
            'event_id'       => $this->id,
            'generated_at'   => $this->date,
            'user'           => $this->user->getId(),
            'token_hash'     => $this->tokenHash,
            'token_shortcut' => $this->tokenShortcut,
            'permissions'    => $this->permissions,
            'active'         => $this->active,
            'expiration'     => $this->expiration,
            'still_valid'    => $this->isStillValid(),
            'description'    => $this->description
        ];
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Checks if provided token hash is same as our Audit Entry
     *
     * @param string $tokenHash
     *
     * @return bool
     */
    public function hasSameTokenHashAs(string $tokenHash): bool
    {
        return $this->tokenHash === $tokenHash;
    }

    /**
     * Deactivate/revoke the token
     */
    public function revokeSelf(): void
    {
        $this->active = false;
    }

    public function getPermissions(): Permissions
    {
        return $this->permissions;
    }
}
