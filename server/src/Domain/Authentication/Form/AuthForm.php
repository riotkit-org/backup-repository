<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

use App\Domain\Roles;

class AuthForm
{
    public ?string $email    = '';
    public ?string $password = '';

    /**
     * @var string[]
     */
    public $roles;

    /**
     * @var DetailsForm
     */
    public $data;

    /**
     * @var string|null
     */
    public $expires;

    /**
     * Optional organization name if the user is organized under any organization eg. "Anarchist Federation"
     *
     * @var string
     */
    public $organization;

    /**
     * Short information in few sentences about the user
     *
     * @var string
     */
    public $about;

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
