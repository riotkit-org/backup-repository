<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonValueException;
use App\Domain\Roles as RolesConst;

class Roles implements \JsonSerializable
{
    /**
     * @var string[]
     */
    protected array $value = [];

    private bool  $alreadyGrantedAdminAccess = false;
    private array $requestedRoles            = [];

    /**
     * @param array $roles
     *
     * @return static
     *
     * @throws CommonValueException
     */
    public static function fromArray(array $roles)
    {
        $availableRoles = static::getAvailableRoles();

        foreach ($roles as $role) {
            if (!\in_array($role, $availableRoles, true)) {
                throw CommonValueException::fromInvalidRolesSelected($role, $availableRoles);
            }
        }

        $new = new static();
        $new->value = $roles;
        $new->prepareAdministrationRole();

        return $new;
    }

    /**
     * @return static
     */
    public static function createEmpty()
    {
        return new static();
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * @param Roles $roles
     *
     * @return static
     */
    public function mergeWith(Roles $roles)
    {
        $new = clone $this;
        $new->value = array_unique(array_merge($this->value, $roles->value));
        $new->prepareAdministrationRole();

        return $new;
    }

    public function getAsList(): array
    {
        if ($this->isAdmin()) {
            return array_values(array_unique(array_merge($this->value, $this->getAdministratorPrivileges())));
        }

        return $this->value;
    }

    public function hasRole(string $roleName): bool
    {
        $this->prepareAdministrationRole();
        $this->recordRoleRequest($roleName);

        return \in_array($roleName, $this->getAsList(), true);
    }

    private function isAdmin(): bool
    {
        // NOTICE: cannot use hasRole() because of possible infinite recursion
        return in_array(RolesConst::PERMISSION_ADMINISTRATOR, $this->value);
    }

    private function getAdministratorPrivileges(): array
    {
        return RolesConst::GRANTS_LIST;
    }

    /**
     * Lists all recorded hasRole() calls
     *
     * @return array
     */
    public function getRequestedRolesList(): array
    {
        return $this->requestedRoles;
    }

    protected static function getAvailableRoles(): array
    {
        return RolesConst::getRolesList();
    }

    private function recordRoleRequest(string $roleName): void
    {
        if (!\in_array($roleName, RolesConst::getRolesList(), true)) {
            return;
        }

        $this->requestedRoles[] = $roleName;
    }

    private function prepareAdministrationRole(): void
    {
        if (!$this->alreadyGrantedAdminAccess && in_array(RolesConst::PERMISSION_ADMINISTRATOR, $this->value, true)) {
            $this->value = array_merge($this->value, RolesConst::GRANTS_LIST);
            $this->alreadyGrantedAdminAccess = true;
        }
    }
}
