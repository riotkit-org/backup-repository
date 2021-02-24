<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonValueException;
use App\Domain\PermissionsReference;

class Permissions implements \JsonSerializable
{
    /**
     * @var string[]
     */
    protected array $value = [];

    private bool  $alreadyGrantedAdminAccess = false;
    private array $requestedPermissions      = [];

    /**
     * @param array $permissionsToSet
     *
     * @return static
     *
     * @throws CommonValueException
     */
    public static function fromArray(array $permissionsToSet)
    {
        $availablePermissions = static::getAvailablePermissions();

        foreach ($permissionsToSet as $role) {
            if (!\in_array($role, $availablePermissions, true)) {
                throw CommonValueException::fromInvalidPermissionsSelected($role, $availablePermissions);
            }
        }

        $new = new static();
        $new->value = $permissionsToSet;
        $new->prepareAdministrationPermission();

        return $new;
    }

    /**
     * @return static
     */
    public static function createEmpty()
    {
        return new static();
    }

    public function jsonSerialize(): array
    {
        return $this->value;
    }

    /**
     * @param Permissions $permissions
     *
     * @return static
     */
    public function mergeWith(Permissions $permissions)
    {
        $new = clone $this;
        $new->value = array_unique(array_merge($this->value, $permissions->value));
        $new->prepareAdministrationPermission();

        return $new;
    }

    public function getAsList(): array
    {
        if ($this->isAdmin()) {
            return array_values(array_unique(array_merge($this->value, $this->getAdministratorPrivileges())));
        }

        return $this->value;
    }

    public function has(string $roleName): bool
    {
        $this->prepareAdministrationPermission();
        $this->recordPermissionRequest($roleName);

        return \in_array($roleName, $this->getAsList(), true);
    }

    private function isAdmin(): bool
    {
        // NOTICE: cannot use hasRole() because of possible infinite recursion
        return in_array(PermissionsReference::PERMISSION_ADMINISTRATOR, $this->value);
    }

    private function getAdministratorPrivileges(): array
    {
        return PermissionsReference::GRANTS_LIST;
    }

    /**
     * Lists all recorded hasRole() calls
     *
     * @return array
     */
    public function getRequestedPermissionsAsList(): array
    {
        return $this->requestedPermissions;
    }

    protected static function getAvailablePermissions(): array
    {
        return PermissionsReference::getPermissionsList();
    }

    private function recordPermissionRequest(string $name): void
    {
        if (!\in_array($name, PermissionsReference::getPermissionsList(), true)) {
            return;
        }

        $this->requestedPermissions[] = $name;
    }

    private function prepareAdministrationPermission(): void
    {
        if (!$this->alreadyGrantedAdminAccess && in_array(PermissionsReference::PERMISSION_ADMINISTRATOR, $this->value, true)) {
            $this->value = array_merge($this->value, PermissionsReference::GRANTS_LIST);
            $this->alreadyGrantedAdminAccess = true;
        }
    }
}
