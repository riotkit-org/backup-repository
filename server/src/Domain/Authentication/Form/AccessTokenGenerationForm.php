<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

use App\Domain\Authentication\Entity\User;

class AccessTokenGenerationForm
{
    public User $user;

    /**
     * List of roles requested to have for a token
     *
     * @var array $requestedRoles
     */
    public array $requestedRoles;

    /**
     * Time in seconds how long the token should live
     *
     * @var int $ttl
     */
    public int $ttl;
}
