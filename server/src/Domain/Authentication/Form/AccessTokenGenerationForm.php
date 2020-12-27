<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

class AccessTokenGenerationForm
{
    /**
     * List of permissions requested to have for a token
     *
     * @var array $requestedPermissions
     */
    public array $requestedPermissions;

    /**
     * Time in seconds how long the token should live
     *
     * @var int $ttl
     */
    public int $ttl;
}
