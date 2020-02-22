<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Response\RoleSearchResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Authentication\Service\Security\RolesInformationProvider;

class RolesListingHandler
{
    private RolesInformationProvider $provider;

    public function __construct(RolesInformationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @return RoleSearchResponse
     *
     * @throws AuthenticationException
     */
    public function handle(AuthenticationManagementContext $context): RoleSearchResponse
    {
        if (!$context->canUseTechnicalEndpoints()) {
            throw new AuthenticationException(
                'Current token does not allow access to technical endpoints',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        return RoleSearchResponse::createResultsResponse($this->provider->findAllRolesWithTheirDescription());
    }
}
