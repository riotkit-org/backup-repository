<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

class AccessTokenGenerationForm
{
    // @todo: Add required fields validation (my implementing an interface method?)

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

    /**
     * Optional token description
     *
     * @var string $description
     */
    public string $description = '';
}
