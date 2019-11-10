<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Authentication\Service\Security\RolesInformationProvider;
use App\Domain\Roles;

class RolesListingHandler
{
    /**
     * @var RolesInformationProvider
     */
    private $provider;

    public function __construct(RolesInformationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    public function handle(AuthenticationManagementContext $context): array
    {
        if (!$context->canUseTechnicalEndpoints()) {
            throw new AuthenticationException(
                'Current token does not allow access to technical endpoints',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        return [
            'roles' => $this->provider->findAllRolesWithTheirDescription()
        ];
    }
}
