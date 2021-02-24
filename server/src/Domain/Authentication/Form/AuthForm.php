<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

class AuthForm
{
    public ?string $email    = '';
    public ?string $password = '';

    // <edit mode>
    public string $repeatPassword = '';
    public string $currentPassword = '';
    // </edit mode>

    /**
     * @var string[]
     */
    public $permissions;

    /**
     * @var RestrictionsForm
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
     * Custom token id (requires additional permissions to use in CREATION endpoint)
     *
     * @var string|null
     */
    public $id;
}
