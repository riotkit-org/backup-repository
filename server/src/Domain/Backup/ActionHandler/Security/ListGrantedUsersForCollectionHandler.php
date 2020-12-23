<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Security;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Form\CollectionTokenListingForm;
use App\Domain\Backup\Repository\UserAccessRepository;
use App\Domain\Backup\Response\Collection\AllowedTokensResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class ListGrantedUsersForCollectionHandler
{
    private UserAccessRepository $repository;

    public function __construct(UserAccessRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CollectionTokenListingForm  $form
     * @param CollectionManagementContext $ctx
     *
     * @throws AuthenticationException
     *
     * @return ?AllowedTokensResponse
     */
    public function handle(CollectionTokenListingForm $form, CollectionManagementContext $ctx): ?AllowedTokensResponse
    {
        if (!$form->collection) {
            return null;
        }

        $this->assertHasRights($ctx, $form->collection);

        return AllowedTokensResponse::createSuccessfulResponse(
            $this->repository->findAllAccessesForCollection($form->collection)
        );
    }

    private function assertHasRights(CollectionManagementContext $securityContext, BackupCollection $collection): void
    {
        if (!$securityContext->canListCollectionTokens($collection)) {
            throw AuthenticationException::fromForbiddenTokenListingInCollectionCause();
        }
    }
}
