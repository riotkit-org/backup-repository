<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Response\PermissionsSearchResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Authentication\Service\PermissionsFilter;
use App\Domain\Authentication\Service\Security\PermissionsInformationProvider;

class RolesListingHandler
{
    private PermissionsInformationProvider $provider;

    public function __construct(PermissionsInformationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param User  $user
     * @param array $limits
     *
     * @return PermissionsSearchResponse
     *
     * @throws AuthenticationException
     */
    public function handle(AuthenticationManagementContext $context, User $user, array $limits): PermissionsSearchResponse
    {
        if (!$context->canListRoles()) {
            throw AuthenticationException::fromPermissionDeniedToListPermissions();
        }

        $allPermissions = $this->provider->findAllRolesWithTheirDescription();

        return PermissionsSearchResponse::createResultsResponse(
            PermissionsFilter::filterBy($allPermissions, $limits, $user),
            $allPermissions
        );
    }
}
