<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Response\RoleSearchResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Authentication\Service\RolesFilter;
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
     * @param User  $user
     * @param array $limits
     *
     * @return RoleSearchResponse
     *
     * @throws AuthenticationException
     */
    public function handle(AuthenticationManagementContext $context, User $user, array $limits): RoleSearchResponse
    {
        if (!$context->canUseTechnicalEndpoints()) {
            throw AuthenticationException::fromPermissionDeniedToUseTechnicalEndpoints();
        }

        return RoleSearchResponse::createResultsResponse(
            RolesFilter::filterBy($this->provider->findAllRolesWithTheirDescription(), $limits, $user)
        );
    }
}
