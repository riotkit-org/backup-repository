<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

use App\Domain\Roles;

class AuthForm
{
    /**
     * @var string[]
     */
    public $roles;

    /**
     * @var TokenDetailsForm
     */
    public $data;

    /**
     * @var string|null
     */
    public $expires;

    public static function getAvailableRoles(): array
    {
        return Roles::ROLES_LIST;
    }
}
