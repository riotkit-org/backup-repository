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

    /**
     * Custom token id (requires additional permissions to use)
     *
     * @var string|null
     */
    public $id;

    public static function getAvailableRoles(): array
    {
        return Roles::getRolesList();
    }
}
