<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;
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
     * @param string $fieldName
     *
     * @return static
     *
     * @throws DomainInputValidationConstraintViolatedError
     */
    public static function fromArray(array $roles, string $fieldName = 'roles')
    {
        $availableRoles = RolesConst::getRolesList();

        foreach ($roles as $role) {
            if (!\in_array($role, $availableRoles, true)) {
                throw DomainInputValidationConstraintViolatedError::fromString(
                    $fieldName,
                    Errors::ERR_MSG_USER_ROLE_INVALID,
                    Errors::ERR_USER_ROLE_INVALID
                );
            }
        }

        $new = new static();
        $new->value = $roles;

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

    public function mergeWith(Roles $roles)
    {
        $this->value = array_unique(array_merge($this->value, $roles->value));
    }

    public function getAsList(): array
    {
        if (!$this->alreadyGrantedAdminAccess && in_array(RolesConst::ROLE_ADMINISTRATOR, $this->value, true)) {
            $this->value = array_merge($this->value, RolesConst::GRANTS_LIST);
            $this->alreadyGrantedAdminAccess = true;
        }

        return $this->value;
    }

    public function hasRole(string $roleName): bool
    {
        $this->recordRoleRequest($roleName);

        return \in_array($roleName, $this->value, true);
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

    private function recordRoleRequest(string $roleName): void
    {
        if (!\in_array($roleName, RolesConst::getRolesList(), true)) {
            return;
        }

        $this->requestedRoles[] = $roleName;
    }
}
