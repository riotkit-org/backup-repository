<?php declare(strict_types=1);

namespace App\Domain\Common\Service\Security;

use App\Domain\PermissionsReference;

class PermissionsInformationProvider
{
    /**
     * @return array
     */
    public function findAllRolesWithTheirDescription(): array
    {
        $ref = new \ReflectionClass(PermissionsReference::class);
        $roles = [];

        foreach ($ref->getConstants() as $constant => $value) {
            if (!\is_string($value)) {
                continue;
            }

            $roles[$value] = $this->findRoleDescription($constant);
        }

        return $roles;
    }

    private function findRoleDescription(string $role): ?string
    {
        $ref = new \ReflectionClassConstant(PermissionsReference::class, $role);

        if (!$ref) {
            return null;
        }

        return trim($ref->getDocComment() ?: '', ' */');
    }
}
