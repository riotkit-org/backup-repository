<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Form\AccessTokenListingForm;
use App\Domain\Authentication\Repository\AccessTokenAuditRepository;
use App\Domain\Authentication\Response\AccessTokenListingResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

/**
 * Access Token Listing
 * ====================
 *   Lists all granted JWT for given user, so the user can see all the sessions and have a possibility to revoke any of them
 */
class AccessTokenListingHandler
{
    private AccessTokenAuditRepository $repository;

    public function __construct(AccessTokenAuditRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param AccessTokenListingForm $form
     *
     * @return AccessTokenListingResponse
     *
     * @throws AuthenticationException
     */
    public function handle(AuthenticationManagementContext $context, AccessTokenListingForm $form): AccessTokenListingResponse
    {
        if (!$form->user) {
            $form->user = $context->getContextUser();
        }

        $this->assertHasRights($context, $form->user);

        return AccessTokenListingResponse::createResultsResponse(
            $this->repository->findForUser($form->user, $form->page, $form->perPageLimit),
            $this->repository->findMaxPagesForUser($form->user),
            $form->page,
            $form->perPageLimit
        );
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param User $user
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context, User $user): void
    {
        if (!$context->canListUserAccessTokens($user)) {
            throw AuthenticationException::fromCannotListAccessTokensOfUser();
        }
    }
}
